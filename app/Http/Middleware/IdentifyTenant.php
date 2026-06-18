<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * IdentifyTenant Middleware
 *
 * Middleware global yang berjalan di setiap request untuk mendeteksi
 * subdomain tenant dari Host header, kemudian mengaktifkan koneksi
 * database yang tepat melalui TenantManager::switchTo().
 *
 * Tahap cleanup (terminate()) memastikan koneksi ditutup dengan rapi
 * setelah setiap request selesai — penting untuk environment seperti
 * Laravel Octane yang menggunakan persistent worker.
 */
class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * Deteksi subdomain dari Host header request. Jika subdomain valid
     * dan tenant aktif ditemukan di landlord DB, aktifkan konteks tenant.
     * Jika tidak, fallback ke konteks landlord.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $host   = $request->getHost();
        $parts  = explode('.', $host);
        $tenant = null;

        // Deteksi subdomain: tokobudi.localhost, ninsky.flashbot.id, dll.
        // Filter: abaikan "www", "localhost" langsung, dan alamat IP
        if (
            count($parts) > 1
            && $parts[0] !== 'www'
            && $parts[0] !== 'localhost'
            && !filter_var($host, FILTER_VALIDATE_IP)
        ) {
            $subdomain = $parts[0];

            try {
                $tenant = Tenant::where('subdomain', $subdomain)->first();
                Log::info('[DEBUG] IdentifyTenant found tenant:', ['subdomain' => $subdomain, 'found' => $tenant !== null, 'is_active' => $tenant ? $tenant->is_active : null]);

                if (!$tenant) {
                    Log::channel('tenant_security')->warning('[IdentifyTenant] Subdomain tidak dikenali.', [
                        'subdomain' => $subdomain,
                        'host'      => $host,
                        'ip'        => $request->ip(),
                        'url'       => $request->fullUrl(),
                    ]);
                    
                    // Only abort if they are trying to access a tenant-specific route
                } elseif (!$tenant->is_active) {
                    Log::channel('tenant_security')->warning('[IdentifyTenant] Tenant tidak aktif diakses.', [
                        'subdomain' => $subdomain,
                        'host'      => $host,
                    ]);
                    
                    // We still bind the tenant context so that subsequent middleware (like EnsurePortalTenant)
                    // or controllers can handle the inactive state properly and show a custom 403 page.
                }
            } catch (\Exception $e) {
                Log::error('[IdentifyTenant] Database landlord bermasalah: ' . $e->getMessage());
                abort(503, 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.');
            }
        }

        if ($tenant) {
            // Aktifkan konteks tenant — koneksi scoped per-request via IoC
            TenantManager::switchTo($tenant);
            
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('identitas_tokos')) {
                    \Illuminate\Support\Facades\View::share('identitasToko', \App\Models\IdentitasToko::first());
                } else {
                    \Illuminate\Support\Facades\View::share('identitasToko', null);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\View::share('identitasToko', null);
            }
        } else {
            // Tidak ada tenant → gunakan landlord context
            TenantManager::switchToLandlord();
        }

        return $next($request);
    }

    /**
     * Perform any final actions after the response is sent to the browser.
     *
     * Dipanggil SETELAH response dikirim ke browser. Membersihkan koneksi
     * tenant dan IoC instances untuk mencegah memory leak di lingkungan
     * long-running (Octane, Swoole, RoadRunner).
     *
     * Pada PHP-FPM biasa, ini bersifat "best effort" — request sudah
     * selesai tapi cleanup tetap baik untuk konsistensi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\Response $response
     * @return void
     */
    public function terminate(Request $request, $response): void
    {
        TenantManager::forgetTenant();
    }
}
