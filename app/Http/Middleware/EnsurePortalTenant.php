<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePortalTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (!app()->bound('current_tenant')) {
            abort(404, 'Toko ini tidak terdaftar di Tenanta.id.');
        }

        $tenant = app('current_tenant');

        if (!$tenant->is_active) {
            abort(403, 'Langganan toko ini sedang tidak aktif. Hubungi pemilik toko.');
        }

        // Opsional: Cek expired
        if ($tenant->subscription_ends_at && $tenant->subscription_ends_at < now()) {
            abort(403, 'Langganan toko ini telah kedaluwarsa. Hubungi pemilik toko.');
        }

        return $next($request);
    }
}
