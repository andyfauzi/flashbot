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
     * @param  Tenant $tenant
     * @return void
     */
    public static function switchToTenant(Tenant $tenant): void
    {
        $connectionName = 'tenant_' . $tenant->id;

        $existing = config("database.connections.{$connectionName}.database");
        if ($existing !== $tenant->database_name) {
            config([
                "database.connections.{$connectionName}" => array_merge(
                    config('database.connections.landlord'),
                    [
                        'database' => $tenant->database_name,
                        'prefix'   => '',
                    ]
                ),
            ]);

            DB::purge($connectionName);
        }

        // Simpan ke IoC container
        app()->instance('current_tenant', $tenant);
        app()->instance('current_tenant_connection', $connectionName);

        // Set koneksi default ke tenant agar CLI/Queue otomatis pakai ini
        DB::setDefaultConnection($connectionName);

        // Update Redis prefix untuk isolasi cache/session/queue
        $redisPrefix = 'tenanta_' . $tenant->id . '_';
        config(['database.redis.options.prefix' => $redisPrefix]);

        Log::channel('tenant_security')->info('[TenantManager] Tenant context aktif.', [
            'tenant_id' => $tenant->id,
            'connection' => $connectionName,
        ]);
    }

    /**
     * Alias untuk kompatibilitas ke belakang
     */
    public static function switchTo(Tenant $tenant): void
    {
        self::switchToTenant($tenant);
    }

    /**
     * Memutuskan koneksi tenant dan mengembalikan ke landlord.
     * Membersihkan cache koneksi dan Redis prefix.
     *
     * @return void
     */
    public static function disconnectTenant(): void
    {
        if (app()->bound('current_tenant_connection')) {
            $connectionName = app('current_tenant_connection');
            DB::purge($connectionName);
            
            // Hapus config koneksi on-the-fly
            $connections = config('database.connections');
            unset($connections[$connectionName]);
            config(['database.connections' => $connections]);
        }

        app()->forgetInstance('current_tenant');
        app()->forgetInstance('current_tenant_connection');

        // Kembalikan default connection ke landlord
        DB::setDefaultConnection('landlord');

        // Kembalikan Redis prefix ke default bawaan config
        $defaultPrefix = env('REDIS_PREFIX', \Illuminate\Support\Str::slug(env('APP_NAME', 'laravel'), '_').'_database_');
        config(['database.redis.options.prefix' => $defaultPrefix]);
    }

    /**
     * Ambil instance tenant yang sedang aktif.
     *
     * @return Tenant|null
     */
    public static function getTenant(): ?Tenant
    {
        return app()->bound('current_tenant') ? app('current_tenant') : null;
    }

    // Mempertahankan alias lama agar kode lain tidak error
    public static function current(): ?Tenant
    {
        return self::getTenant();
    }

    public static function switchToLandlord(): void
    {
        self::disconnectTenant();
    }

    public static function forgetTenant(): void
    {
        self::disconnectTenant();
    }

    /**
     * Ambil nama koneksi tenant yang sedang aktif untuk request ini.
     *
     * @return string
     */
    public static function getTenantConnection(): string
    {
        if (!app()->bound('current_tenant_connection')) {
            throw new \RuntimeException(
                'Tenant context belum diinisialisasi. Model tenant tidak boleh diakses ' .
                'tanpa melewati middleware IdentifyTenant atau memanggil TenantManager::switchToTenant() terlebih dahulu.'
            );
        }

        return app('current_tenant_connection');
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
}
