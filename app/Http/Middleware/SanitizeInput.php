<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    /**
     * Field keys yang dikecualikan dari strip_tags karena butuh HTML/Rich Text.
     */
    protected $except = [
        'deskripsi',
        'pesan',
        'template_pesan',
        'html',
        'body',
        'content',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        if (!empty($input)) {
            array_walk_recursive($input, function (&$value, $key) {
                if (is_string($value)) {
                    // 1. Reject Null Bytes
                    if (strpos($value, "\0") !== false) {
                        abort(400, 'Invalid characters detected in input.');
                    }

                    // 2. Strip Tags (Kecuali field tertentu)
                    if (!in_array($key, $this->except, true)) {
                        $value = strip_tags($value);
                    }

                    // 3. Trim whitespace
                    $value = trim($value);
                }
            });

            $request->merge($input);
        }

        return $next($request);
    }
}
