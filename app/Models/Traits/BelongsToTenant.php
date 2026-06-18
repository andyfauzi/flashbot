<?php

namespace App\Models\Traits;

use App\Services\TenantManager;
use Illuminate\Support\Facades\Log;

/**
 * Trait BelongsToTenant
 *
 * Diterapkan pada SEMUA model yang datanya berada di database tenant (bukan landlord).
 * Trait ini memastikan setiap query Eloquent secara otomatis menggunakan koneksi
 * database yang tepat untuk tenant yang sedang aktif pada request saat ini.
 *
 * Dengan pendekatan ini, koneksi bersifat SCOPED PER-REQUEST melalui IoC container
 * (bukan global Config/DB::setDefaultConnection), sehingga aman dari race condition
 * di lingkungan PHP-FPM multi-worker.
 *
 * Cara penggunaan:
 * ```php
 * class Produk extends Model
 * {
 *     use BelongsToTenant;
 *     // ...
 * }
 * ```
 */
trait BelongsToTenant
{
    /**
     * Override Eloquent connection resolver untuk model ini.
     *
     * Dipanggil oleh Eloquent setiap kali model perlu mengetahui
     * koneksi database mana yang harus digunakan.
     *
     * @return string Nama koneksi database tenant aktif (misal: "tenant_5")
     *
     * @throws \RuntimeException Jika tidak ada tenant context yang aktif saat ini.
     *                           Ini adalah "fail-loud" behavior — lebih baik crash
     *                           daripada menulis data ke database yang salah.
     */
    public function getConnectionName(): string
    {
        // Jika model mengizinkan landlord fallback dan tidak ada koneksi tenant aktif, kembalikan 'landlord'
        if (property_exists($this, 'allowLandlord') && $this->allowLandlord && !app()->bound('current_tenant_connection')) {
            return 'landlord';
        }
        
        return TenantManager::getTenantConnection();
    }

    /**
     * Hook yang dipanggil saat model di-boot oleh Eloquent.
     *
     * Menambahkan global scope logging untuk membantu audit trail
     * dan mendeteksi akses model tanpa tenant context.
     *
     * @return void
     */
    protected static function bootBelongsToTenant(): void
    {
        // Di mode debug, log setiap query tenant untuk audit
        if (config('app.debug') && app()->bound('current_tenant')) {
            $tenant    = app('current_tenant');
            $modelName = static::class;

            Log::channel('tenant_security')->debug("[BelongsToTenant] Query pada model {$modelName}", [
                'tenant_id'      => $tenant->id,
                'tenant_name'    => $tenant->name,
                'connection'     => TenantManager::getTenantConnection(),
            ]);
        }
    }
}
