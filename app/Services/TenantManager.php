<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * TenantManager — Layanan pengelolaan konteks multi-tenant.
 *
 * ## Desain: Request-Scoped Connection (bukan Global Default Connection)
 *
 * Versi sebelumnya menggunakan `Config::set()` + `DB::setDefaultConnection()` yang
 * bersifat GLOBAL di seluruh proses PHP. Pada PHP-FPM dengan worker pool, ini
 * menyebabkan race condition: request A dan request B yang concurrent bisa saling
 * mengubah koneksi aktif, sehingga query salah tenant.
 *
 * Solusi ini menggunakan NAMED CONNECTIONS per-tenant yang disimpan di IoC container
 * (app()->instance). Karena Laravel membuat container baru untuk setiap request HTTP,
 * nilai ini bersifat scoped per-request dan tidak bocor ke request lain.
 *
 * Skema penamaan koneksi: "tenant_{tenant_id}" — misal "tenant_5" untuk tenant ID 5.
 * Setiap koneksi ini merupakan klon dari konfigurasi 'landlord', hanya berbeda
 * database name-nya.
 */
class TenantManager
{
    /**
     * Aktifkan konteks database untuk tenant yang diberikan.
     *
     * Membuat konfigurasi koneksi bernama unik ("tenant_{id}") yang merupakan
     * klon dari koneksi 'landlord' dengan database name yang diganti ke database
     * milik tenant tersebut. Koneksi ini disimpan di IoC container sehingga
     * scoped per-request dan tidak mempengaruhi request lain.
     *
     * @param  Tenant $tenant  Tenant yang akan diaktifkan konteksnya.
     * @return void
     */
    public static function switchTo(Tenant $tenant): void
    {
        // Nama koneksi unik per tenant: "tenant_5", "tenant_12", dll.
        // Menggunakan ID (bukan subdomain) agar immutable.
        $connectionName = 'tenant_' . $tenant->id;

        // Hanya konfigurasi koneksi jika belum ada (idempotent),
        // atau jika databasenya berbeda (tenant berganti di tengah request).
        $existing = config("database.connections.{$connectionName}.database");
        if ($existing !== $tenant->database_name) {
            config([
                "database.connections.{$connectionName}" => array_merge(
                    // Klon semua parameter dari koneksi landlord (host, port, user, pass, charset, dll)
                    config('database.connections.landlord'),
                    [
                        // Override hanya nama databasenya
                        'database' => $tenant->database_name,
                        'prefix'   => '',
                    ]
                ),
            ]);

            // Purge koneksi lama jika ada (agar tidak pakai PDO handle lama)
            DB::purge($connectionName);
        }

        // Simpan di IoC container — SCOPED PER REQUEST, tidak global.
        // Laravel membuat container baru untuk setiap request HTTP,
        // jadi nilai ini tidak bocor ke request concurrent lain.
        app()->instance('current_tenant', $tenant);
        app()->instance('current_tenant_connection', $connectionName);

        // Catat setiap tenant switch ke dedicated security log
        Log::channel('tenant_security')->info('[TenantManager] Tenant context aktif.', [
            'tenant_id'      => $tenant->id,
            'tenant_name'    => $tenant->name,
            'subdomain'      => $tenant->subdomain,
            'database'       => $tenant->database_name,
            'connection'     => $connectionName,
        ]);
    }

    /**
     * Aktifkan konteks landlord (database utama platform).
     *
     * Dipanggil untuk route yang tidak memiliki subdomain tenant,
     * seperti landing page, OAuth callback, atau super-admin panel.
     *
     * @return void
     */
    public static function switchToLandlord(): void
    {
        // Hapus tenant context dari container
        app()->forgetInstance('current_tenant');
        app()->forgetInstance('current_tenant_connection');

        // Pastikan default connection kembali ke landlord
        // (ini aman karena tidak ada tenant lain yang bergantung padanya)
        DB::setDefaultConnection('landlord');
    }

    /**
     * Ambil nama koneksi tenant yang sedang aktif untuk request ini.
     *
     * Dipanggil oleh trait BelongsToTenant di setiap model.
     *
     * @return string  Nama koneksi, misal "tenant_5".
     *
     * @throws \RuntimeException Jika tidak ada tenant context aktif.
     *                           SENGAJA fail-loud: lebih baik exception daripada
     *                           menulis data ke database yang salah secara silent.
     */
    public static function getTenantConnection(): string
    {
        if (!app()->bound('current_tenant_connection')) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2] ?? [];

            Log::channel('tenant_security')->error('[TenantManager] Model diakses tanpa tenant context!', [
                'caller_class'  => $caller['class'] ?? 'unknown',
                'caller_method' => $caller['function'] ?? 'unknown',
                'caller_file'   => $caller['file'] ?? 'unknown',
                'caller_line'   => $caller['line'] ?? 0,
            ]);

            throw new \RuntimeException(
                'Tenant context belum diinisialisasi. Model tenant tidak boleh diakses ' .
                'tanpa melewati middleware IdentifyTenant atau memanggil TenantManager::switchTo() terlebih dahulu.'
            );
        }

        return app('current_tenant_connection');
    }

    /**
     * Ambil instance tenant yang sedang aktif.
     *
     * @return Tenant|null
     */
    public static function current(): ?Tenant
    {
        return app()->bound('current_tenant') ? app('current_tenant') : null;
    }

    /**
     * Cek apakah saat ini ada tenant context yang aktif.
     *
     * @return bool
     */
    public static function hasTenant(): bool
    {
        return app()->bound('current_tenant_connection');
    }

    /**
     * Bersihkan tenant context dan tutup koneksi setelah request selesai.
     *
     * Dipanggil dari terminate() di IdentifyTenant middleware untuk
     * memastikan koneksi database dikembalikan ke pool setelah setiap request.
     *
     * @return void
     */
    public static function forgetTenant(): void
    {
        if (app()->bound('current_tenant_connection')) {
            $connectionName = app('current_tenant_connection');
            $tenant         = app()->bound('current_tenant') ? app('current_tenant') : null;

            // Tutup dan hapus koneksi dari pool Eloquent
            DB::purge($connectionName);

            // Hapus konfigurasi koneksi dari config runtime untuk bersih-bersih memori
            // (opsional, tapi baik untuk lingkungan long-running seperti Octane)
            $connections = config('database.connections');
            unset($connections[$connectionName]);
            config(['database.connections' => $connections]);

            // Hapus dari IoC container
            app()->forgetInstance('current_tenant');
            app()->forgetInstance('current_tenant_connection');

            if ($tenant) {
                Log::channel('tenant_security')->debug('[TenantManager] Tenant context dibersihkan.', [
                    'tenant_id'  => $tenant->id,
                    'connection' => $connectionName,
                ]);
            }
        }
    }
}
