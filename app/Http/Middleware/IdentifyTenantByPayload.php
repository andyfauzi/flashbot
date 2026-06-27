<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IdentifyTenantByPayload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Ambil device_id dari Header atau JSON Body
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');

        if (!$deviceId) {
            Log::channel('tenant_security')->warning('[Webhook] Akses ditolak. Missing device_id.', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return response()->json(['error' => 'Missing device_id identifier'], 401);
        }

        try {
            // Cari device di central DB 'chatbot_devices'
            // Tabel ini harus ada di database landlord agar mudah di-resolve
            $device = DB::connection('landlord')
                ->table('chatbot_devices') // Sesuaikan nama tabel jika berbeda
                ->where('uuid', $deviceId) // atau 'device_id', sesuaikan skema
                ->orWhere('id', $deviceId)
                ->first();

            if (!$device) {
                Log::channel('tenant_security')->warning('[Webhook] Device tidak terdaftar di sistem.', [
                    'device_id' => $deviceId,
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'Unauthorized device'], 401);
            }

            // Ambil tenant_id
            $tenantId = $device->tenant_id;
            
            $tenant = Tenant::find($tenantId);

            if (!$tenant || !$tenant->is_active) {
                Log::channel('tenant_security')->warning('[Webhook] Tenant tidak aktif atau tidak ditemukan.', [
                    'tenant_id' => $tenantId,
                    'device_id' => $deviceId,
                ]);
                return response()->json(['error' => 'Tenant inactive or not found'], 403);
            }

            // Aktifkan koneksi tenant
            TenantManager::switchToTenant($tenant);

        } catch (\Exception $e) {
            Log::error('[Webhook] Resolusi tenant bermasalah: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error resolving tenant'], 500);
        }

        return $next($request);
    }
    
    public function terminate(Request $request, $response): void
    {
        TenantManager::forgetTenant();
    }
}
