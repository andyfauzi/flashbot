<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
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
        $response = $next($request);

        // Hanya terapkan header jika response berupa object Response/JsonResponse
        if (method_exists($response, 'headers')) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
            
            // Content Security Policy
            // Jika ada resource eksternal yang di-load (contoh: CDN Bootstrap, FontAwesome, Google Fonts),
            // Anda mungkin perlu menyesuaikan CSP ini di masa depan.
            $response->headers->set('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data: blob:; object-src 'none';");

            // Strict-Transport-Security (HSTS) untuk production
            if (app()->environment('production')) {
                $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }
        }

        return $response;
    }
}
