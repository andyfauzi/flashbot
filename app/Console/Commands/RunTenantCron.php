<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Log;

class RunTenantCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenanta:cron {action : Logika spesifik yang ingin dieksekusi (contoh: generate-report, cancel-unpaid)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan tugas cron yang diisolasi dengan aman untuk setiap tenant aktif';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        $this->info("Memulai proses tenanta:cron dengan action [{$action}]...");
        
        $tenants = DB::connection('landlord')->table('tenants')->where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Tidak ada tenant aktif ditemukan.');
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($tenants as $tenantData) {
            $this->line("Memproses tenant: {$tenantData->name} (ID: {$tenantData->id})");
            
            try {
                // Konversi stdClass ke Model agar valid di method switchToTenant
                $tenant = Tenant::find($tenantData->id);
                
                if (!$tenant) {
                    throw new \Exception("Model Tenant tidak ditemukan di DB.");
                }

                // 1. Switch context ke tenant ini
                TenantManager::switchToTenant($tenant);

                // 2. Eksekusi logika bisnis berdasarkan parameter action
                $this->executeAction($action, $tenant);

                $successCount++;
                $this->info("  [OK] Berhasil diproses.");
                
            } catch (\Exception $e) {
                $failCount++;
                $this->error("  [GAGAL] Error: " . $e->getMessage());
                Log::error("[Cron:{$action}] Gagal pada tenant {$tenantData->id}: " . $e->getMessage());
            } finally {
                // 3. SELALU kembalikan koneksi ke landlord di blok finally
                // Ini krusial agar tenant berikutnya tidak menggunakan koneksi tenant sebelumnya
                // yang mungkin sudah dalam state yang rusak (misal setelah exception)
                TenantManager::disconnectTenant();
            }
        }

        $this->info('---');
        $this->info("Proses selesai. Sukses: {$successCount}, Gagal: {$failCount}");

        return Command::SUCCESS;
    }
    
    /**
     * Eksekusi logika cron spesifik di sini.
     */
    protected function executeAction($action, Tenant $tenant)
    {
        switch ($action) {
            case 'cancel-unpaid':
                // Contoh: Membatalkan order yang belum dibayar lebih dari 15 menit
                // DB::table('transaksis')->where('status', 'unconfirmed')->where('created_at', '<', now()->subMinutes(15))->update(['status' => 'cancelled']);
                break;
                
            case 'generate-report':
                // Contoh: Generate laporan harian
                break;
                
            case 'test':
                // Hanya untuk uji coba bahwa perpindahan database aman
                $userCount = DB::table('users')->count();
                $this->line("    -> Jumlah user di tabel tenant ini: {$userCount}");
                break;

            default:
                throw new \Exception("Action [{$action}] tidak dikenali.");
        }
    }
}
