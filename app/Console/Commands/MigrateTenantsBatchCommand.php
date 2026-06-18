<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrateTenantsBatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-tenants-batch {--chunk=50 : Jumlah tenant per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan migrasi database tenant secara batching untuk mencegah Out Of Memory (OOM) saat jumlah tenant mencapai ribuan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $chunkSize = (int) $this->option('chunk');
        $this->info("Memulai migrasi batching tenant dengan ukuran chunk: {$chunkSize}");

        $totalTenants = Tenant::count();
        $this->info("Total tenant yang akan dimigrasi: {$totalTenants}");

        $processed = 0;

        Tenant::chunk($chunkSize, function ($tenants) use (&$processed, $totalTenants) {
            foreach ($tenants as $tenant) {
                $this->info("Migrasi Tenant: {$tenant->id} ({$tenant->subdomain})");
                
                try {
                    // Karena menggunakan stancl/tenancy, perintahnya biasanya:
                    Artisan::call('tenants:run', [
                        'commandname' => 'migrate',
                        '--tenants' => [$tenant->id],
                        '--force' => true
                    ]);
                    
                    $this->info(Artisan::output());
                } catch (\Exception $e) {
                    $this->error("Gagal migrasi tenant {$tenant->id}: " . $e->getMessage());
                    Log::error("Gagal migrasi tenant {$tenant->id}: " . $e->getMessage());
                }
                
                $processed++;
            }
            
            $this->info("Progres: {$processed} / {$totalTenants} tenant selesai dimigrasi.");
            
            // Bebaskan memory (OOM Protection)
            gc_collect_cycles();
        });

        $this->info("Proses migrasi batching selesai sepenuhnya.");
    }
}
