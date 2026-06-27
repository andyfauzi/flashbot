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
        $host   = explode(':', $request->getHost())[0];
        $parts  = explode('.', $host);
        $tenant = null;

        $centralDomains = ['tenanta.id', 'localhost', '127.0.0.1'];

        if (
            count($parts) > 1
            && !in_array($host, $centralDomains)
            && $parts[0] !== 'www'
            && !filter_var($host, FILTER_VALIDATE_IP)
        ) {
            $subdomain = $parts[0];

            try {
                // Gunakan koneksi landlord eksplisit untuk mencari tenant
                $tenant = \Illuminate\Support\Facades\DB::connection('landlord')
                    ->table('tenants')
                    ->where('subdomain', $subdomain)
                    ->first();

                if (!$tenant) {
                    Log::channel('tenant_security')->warning('[IdentifyTenant] Subdomain tidak dikenali.', [
                        'subdomain' => $subdomain,
                        'host'      => $host,
                        'ip'        => $request->ip(),
                    ]);
                    
                    abort(404, 'Toko tidak ditemukan.');
                } 
                
                // Cek status tenant / subscription
                if (!$tenant->is_active) {
                    Log::channel('tenant_security')->warning('[IdentifyTenant] Tenant tidak aktif / suspended diakses.', [
                        'subdomain' => $subdomain,
                        'host'      => $host,
                    ]);
                    
                    abort(403, 'Akses ditolak. Masa berlangganan telah berakhir atau akun sedang ditangguhkan.');
                }
            } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
                throw $e; // Re-throw HTTP aborts
            } catch (\Exception $e) {
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    throw $e;
                }
                Log::error('[IdentifyTenant] Database landlord bermasalah: ' . $e->getMessage());
                abort(503, 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.');
            }
        }

        if ($tenant) {
            // Kita perlu mengambil dari model Eloquent untuk konsistensi dengan method di TenantManager
            $tenantModel = Tenant::find($tenant->id);
            
            // Aktifkan konteks tenant
            TenantManager::switchToTenant($tenantModel);
            
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
