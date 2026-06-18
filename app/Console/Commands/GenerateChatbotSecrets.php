<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateChatbotSecrets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashbot:generate-secrets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate secure random secrets for Chatbot Webhooks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Membangkitkan token keamanan untuk konfigurasi Webhook Flashbot...');

        $webhookSecret = Str::random(40);
        $metaVerifyToken = Str::random(32);
        $baileysSecret = Str::random(40);

        $this->line('');
        $this->line('<fg=yellow>=== COPY NILAI BERIKUT KE DALAM FILE .env ANDA ===</>');
        $this->line('');
        $this->line("<fg=green>WEBHOOK_SECRET=</>{$webhookSecret}");
        $this->line("<fg=green>META_VERIFY_TOKEN=</>{$metaVerifyToken}");
        $this->line("<fg=green>BAILEYS_SECRET=</>{$baileysSecret}");
        $this->line('');
        $this->info('⚠️  Catatan: Pastikan Node.js Baileys API juga dikonfigurasi menggunakan WEBHOOK_SECRET dan BAILEYS_SECRET yang sama.');

        return 0;
    }
}
