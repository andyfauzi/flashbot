<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $mode = 'auto'): Response
    {
        if ($mode === 'auto') {
            $mode = $request->has('entry') || $request->has('hub_mode') ? 'meta' : 'baileys';
        }

        if ($mode === 'baileys') {
            $expectedKey = config('chatbot.webhook_secret');
            $providedKey = $request->header('x-api-key', '');

            // Ensure expected key is not empty to avoid accepting empty requests
            if (empty($expectedKey) || !hash_equals($expectedKey, $providedKey)) {
                Log::warning('Unauthorized Baileys webhook attempt', [
                    'ip' => $request->ip(),
                    'timestamp' => now()->toIso8601String(),
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } elseif ($mode === 'meta') {
            // For Meta GET verification
            if ($request->isMethod('GET')) {
                // Verified in controller for now, or could handle here
            } else {
                // Meta POST Payload Validation via HMAC-SHA256 (X-Hub-Signature-256)
                // App Secret from Meta Developer Console
                $appSecret = config('chatbot.webhook_secret'); // or meta_app_secret
                $signatureHeader = $request->header('x-hub-signature-256');

                if ($signatureHeader && !empty($appSecret)) {
                    $signature = str_replace('sha256=', '', $signatureHeader);
                    $payload = $request->getContent();
                    $expectedSignature = hash_hmac('sha256', $payload, $appSecret);

                    if (!hash_equals($expectedSignature, $signature)) {
                        Log::warning('Invalid Meta webhook signature', [
                            'ip' => $request->ip(),
                            'timestamp' => now()->toIso8601String(),
                        ]);
                        return response()->json(['error' => 'Unauthorized'], 401);
                    }
                }
            }
        }

        return $next($request);
    }
}
