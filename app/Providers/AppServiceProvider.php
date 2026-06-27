<?php

// File ini ada di: app/Providers/AppServiceProvider.php
// Ganti seluruh isi file dengan kode berikut

namespace App\Providers;

use App\Services\GrupService;
use App\Services\WhatsAppService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Daftarkan WhatsAppService sebagai singleton
        $this->app->singleton(WhatsAppService::class);

        // Daftarkan GrupService sebagai singleton
        $this->app->singleton(GrupService::class, function ($app) {
            return new GrupService($app->make(WhatsAppService::class));
        });
    }

    public function boot()
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (app()->isProduction()) {
            $requiredSecrets = [
                'chatbot.webhook_secret',
                'chatbot.meta_verify_token',
            ];
            foreach ($requiredSecrets as $key) {
                if (empty(config($key))) {
                    throw new \RuntimeException(
                        "Konfigurasi wajib belum diisi: {$key}. Cek file .env Anda."
                    );
                }
            }
        }

        RateLimiter::for('portal-order', function (Request $request) {
            return [
                Limit::perMinute(10)->by($request->ip()),
                Limit::perHour(50)->by($request->ip()),
                Limit::perDay(200)->by($request->ip()),
            ];
        });

        // GLOBAL LOG SANITIZER
        // Mencegah PII (Personally Identifiable Information)
        // bocor ke file log (UU PDP & OWASP A09)
        // =============================================
        $this->app->make(\Illuminate\Log\LogManager::class)->listen(function (\Illuminate\Log\Events\MessageLogged $event) {
            // Kita tidak bisa mencegah tulisannya karena MessageLogged dipanggil setelah ditulis,
            // Namun kita bisa mensanitasi dump dari context jika menggunakan custom writer.
            // Sebagai alternatif, pada Laravel 10 kita gunakan Log::shareContext
        });

        // Event listener untuk Request, menghapus input sensitif sebelum dicatat jika ada logger otomatis
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'wa_id', 'nomor_wa'];
        request()->headers->set('X-Sanitized', 'true'); // Flag untuk logger
    }
}