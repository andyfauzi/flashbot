<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSalesAgent
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isSales()) {
            return $next($request);
        }

        abort(403, 'Akses Ditolak. Anda bukan mitra sales.');
    }
}
