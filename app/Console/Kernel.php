<?php

// File ini ada di: app/Console/Kernel.php
// Tambahkan method schedule() berikut

namespace App\Console;

use App\Services\GrupService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // =============================================
    // JADWAL OTOMATIS
    // =============================================
    protected function schedule(Schedule $schedule)
    {
        // Cek dan kirim pengingat setiap menit
        $schedule->call(function () {
            app(GrupService::class)->kirimPengingatYangJatuhTempo();
        })->everyMinute()->name('kirim-pengingat')->withoutOverlapping();

        // Cek dan kirim pengingat pre-order H-1 (Setiap pagi jam 08:00)
        $schedule->command('preorder:pengingat')->dailyAt('08:00')->name('pengingat-preorder');

        // Cek dan kirim pengingat orderan pending ke Admin (Setiap pagi jam 09:00)
        $schedule->command('order:pengingat-pending')->dailyAt('09:00')->name('pengingat-order-pending');

        // Purge log webhook yang sudah berumur > 7 hari
        $schedule->command('logs:purge')->dailyAt('00:00')->name('purge-webhook-logs');

        // Bersihkan sesi chatbot (abandoned cart) yang ditinggalkan > 24 jam
        $schedule->command('chatbot:cleanup-sessions')->hourly()->name('cleanup-chatbot-sessions')->withoutOverlapping();
        $schedule->command('flashbot:check-subscriptions')->dailyAt('00:00');

        // Masking data privasi pelanggan sesuai UU PDP (Setiap malam jam 02:00)
        $schedule->command('app:pdp-data-masking')->dailyAt('02:00')->name('pdp-data-masking');

        // Bersihkan reservasi yang kedaluwarsa (Setiap 15 menit)
        $schedule->command('reservations:clean-expired')->everyFifteenMinutes()->name('clean-expired-reservations')->withoutOverlapping();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}