<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Services\TenantManager;
use App\Models\ChatbotOrderSession;
use Illuminate\Support\Facades\Log;

class CleanupChatbotSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:cleanup-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bersihkan state chatbot (abandoned carts) yang sudah ditinggalkan > 24 jam';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Memulai pembersihan sesi chatbot kadaluwarsa...');
        
        TenantManager::switchToLandlord();
        $tenants = Tenant::where('is_active', true)->get();
        
        $totalCleaned = 0;

        foreach ($tenants as $tenant) {
            try {
                TenantManager::switchTo($tenant);
                
                $deletedCount = ChatbotOrderSession::where('updated_at', '<', now()->subHours(24))->delete();
                
                if ($deletedCount > 0) {
                    $this->info("[$tenant->subdomain] Dihapus $deletedCount sesi.");
                    $totalCleaned += $deletedCount;
                }
            } catch (\Exception $e) {
                Log::error("Gagal cleanup sesi chatbot pada tenant {$tenant->id}: " . $e->getMessage());
                $this->error("Gagal pada tenant {$tenant->subdomain}. Cek log untuk detail.");
            }
        }
        
        TenantManager::switchToLandlord();
        
        $this->info("Pembersihan selesai! Total sesi terhapus: $totalCleaned.");
        return 0;
    }
}
