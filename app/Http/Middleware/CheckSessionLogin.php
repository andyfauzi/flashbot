<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSessionLogin
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login (ada di session)
        if (!session()->has('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
