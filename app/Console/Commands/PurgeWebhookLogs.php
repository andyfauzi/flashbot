<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PurgeWebhookLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:purge {--older-than=7 : Hari maksimal umur file log webhook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus log webhook yang berumur lebih dari batas hari yang ditentukan (default: 7 hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('older-than');
        $this->info("Memulai pembersihan file log webhook yang lebih lama dari {$days} hari...");

        $logPath = storage_path('logs');
        $files = File::files($logPath);
        
        $now = now();
        $deletedCount = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Hanya targetkan file webhook, misalnya webhook.log atau webhook-2026-06-11.log
            if (str_starts_with($filename, 'webhook')) {
                // File modified time
                $lastModified = \Carbon\Carbon::createFromTimestamp($file->getMTime());
                $diffInDays = $lastModified->diffInDays($now);

                if ($diffInDays > $days) {
                    $this->line("Menghapus: {$filename} (Umur: {$diffInDays} hari)");
                    File::delete($file->getPathname());
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $this->info("Selesai! {$deletedCount} file log berhasil dihapus.");
        } else {
            $this->info("Tidak ada file log webhook lama yang ditemukan.");
        }

        return 0;
    }
}
