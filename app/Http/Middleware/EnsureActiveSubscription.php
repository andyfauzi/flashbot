<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class EnsureActiveSubscription
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
        // Skip middleware if the user is not authenticated or is a super admin
        if (!auth()->check() || auth()->user()->is_super_admin) {
            return $next($request);
        }

        // Avoid infinite loop if already accessing the billing page or midtrans callback
        if ($request->routeIs('dashboard.billing.*') || $request->routeIs('webhook.*')) {
            return $next($request);
        }

        $tenant = app('current_tenant') ?? auth()->user()->tenant;

        if ($tenant) {
            // Check if plan has expired
            if ($tenant->plan_expires_at && $tenant->plan_expires_at < now()) {
                return redirect()->route('dashboard.billing.index')
                    ->with('error', 'Masa aktif atau masa uji coba paket Anda telah berakhir. Silakan lakukan pembayaran untuk melanjutkan penggunaan aplikasi.');
            }
        }

        return $next($request);
    }
}
