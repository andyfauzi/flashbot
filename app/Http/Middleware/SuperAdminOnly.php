<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SuperAdminOnly
{
    /**
     * Handle an incoming request.
     * Checks:
     *  1. User is authenticated on the landlord DB
     *  2. User has is_super_admin = true in landlord.users
     *  3. Requesting IP is in the SUPER_ADMIN_IPS whitelist
     */
    public function handle(Request $request, Closure $next)
    {
        // ── Guard 1: Must be logged in ──────────────────────────────────────
        if (!Auth::check()) {
            Log::warning('[SuperAdmin] Unauthenticated access attempt.', [
                'ip'  => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(403, 'Akses ditolak: Anda harus login terlebih dahulu.');
        }

        // ── Guard 2: Must have is_super_admin flag in landlord DB ───────────
        // We read directly from the landlord connection to prevent any
        // tenant-context bleed from affecting this critical check.
        $userId = Auth::id();
        $isSuperAdmin = DB::connection('landlord')
            ->table('users')
            ->where('id', $userId)
            ->value('is_super_admin');

        if (!$isSuperAdmin) {
            Log::warning('[SuperAdmin] Unauthorized access attempt (not super admin).', [
                'user_id' => $userId,
                'email'   => Auth::user()->email,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses Super Admin.');
        }

        // ── Guard 3: IP Whitelist check ─────────────────────────────────────
        $allowedIpsRaw = env('SUPER_ADMIN_IPS', '127.0.0.1,::1');
        $allowedIps    = array_map('trim', explode(',', $allowedIpsRaw));
        $clientIp      = $request->ip();

        if (!in_array('*', $allowedIps) && !in_array($clientIp, $allowedIps)) {
            Log::warning('[SuperAdmin] Access blocked: IP not whitelisted.', [
                'user_id'    => $userId,
                'email'      => Auth::user()->email,
                'ip'         => $clientIp,
                'allowed'    => $allowedIps,
                'url'        => $request->fullUrl(),
            ]);
            abort(403, "Akses ditolak: IP Anda ({$clientIp}) tidak terdaftar di whitelist Super Admin.");
        }

        // ── Audit Log: Successful access ────────────────────────────────────
        Log::info('[SuperAdmin] Access granted.', [
            'user_id' => $userId,
            'email'   => Auth::user()->email,
            'ip'      => $clientIp,
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
        ]);

        // ── Update last_super_admin_access timestamp ─────────────────────────
        DB::connection('landlord')
            ->table('users')
            ->where('id', $userId)
            ->update(['last_super_admin_access' => now()]);

        return $next($request);
    }
}
