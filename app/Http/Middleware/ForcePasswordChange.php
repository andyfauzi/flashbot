<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Allow access to password force-change page and logout
            if ($user->must_change_password) {
                if (!$request->routeIs('password.force-change') && 
                    !$request->routeIs('password.force-change.update') && 
                    !$request->routeIs('logout')) {
                    
                    return redirect()->route('password.force-change');
                }
            }
        }

        return $next($request);
    }
}
