<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoLogoutIdle
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity', time());
            $idleTime = time() - $lastActivity;
            
            // 20 minutes = 1200 seconds
            if ($idleTime > 1200) {
                Auth::logout();
                session()->flush();
                return redirect()->route('login')->withErrors(['error' => 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 20 menit. Silakan login kembali.']);
            }
            
            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
