<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use App\Models\SatuanKonversi;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Log;

class SeedSatuanKonversiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-satuan-konversi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeder Satuan Konversi ke semua database tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = Tenant::all();
        $this->info("Menyiapkan Seeder Satuan Konversi untuk {$tenants->count()} tenant.");

        $konversis = [
            ['satuan_awal' => 'Kilogram', 'satuan_akhir' => 'Gram', 'nilai_konversi' => 1000],
            ['satuan_awal' => 'Liter', 'satuan_akhir' => 'ml', 'nilai_konversi' => 1000],
            ['satuan_awal' => 'Galon (19L)', 'satuan_akhir' => 'ml', 'nilai_konversi' => 19000],
        ];

        foreach ($tenants as $tenant) {
            $this->info("Seeding Tenant: {$tenant->subdomain}");
            try {
                TenantManager::switchToTenant($tenant);

                foreach ($konversis as $konversi) {
                    SatuanKonversi::firstOrCreate([
                        'satuan_awal' => $konversi['satuan_awal'],
                        'satuan_akhir' => $konversi['satuan_akhir'],
                    ], $konversi);
                }

                TenantManager::disconnectTenant();
            } catch (\Exception $e) {
                $this->error("Gagal seeding tenant {$tenant->subdomain}: " . $e->getMessage());
                Log::error("Gagal seeding tenant {$tenant->subdomain}: " . $e->getMessage());
                TenantManager::disconnectTenant();
            }
        }

        $this->info("Seeding selesai!");
    }
}
