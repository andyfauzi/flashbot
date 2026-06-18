<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProvisionTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 300; // 5 minutes max

    protected $tenantId;
    protected $dbName;
    protected $ownerName;
    protected $ownerEmail;
    protected $googleId;
    protected $storeAddress;
    protected $whatsappNumber;
    protected $jenisLayanan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tenantId, $dbName, $ownerName, $ownerEmail, $googleId, $storeAddress, $whatsappNumber, $jenisLayanan)
    {
        $this->tenantId = $tenantId;
        $this->dbName = $dbName;
        $this->ownerName = $ownerName;
        $this->ownerEmail = $ownerEmail;
        $this->googleId = $googleId;
        $this->storeAddress = $storeAddress;
        $this->whatsappNumber = $whatsappNumber;
        $this->jenisLayanan = $jenisLayanan;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // 1. Create database
            DB::connection('landlord')->statement("CREATE DATABASE `{$this->dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 2. Set dynamic connection and run migrations
            config(['database.connections.tenant.database' => $this->dbName]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--realpath' => false,
                '--force' => true,
            ]);

            $tempPassword = Str::random(10);

            // 3. Create default owner user linked with Google ID
            DB::connection('tenant')->table('users')->insert([
                'name' => $this->ownerName,
                'email' => $this->ownerEmail,
                'password' => bcrypt($tempPassword), // Real temp password
                'google_id' => $this->googleId,
                'role' => 'owner',
                'must_change_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create default Identitas Toko
            $tenantData = DB::connection('landlord')->table('tenants')->where('id', $this->tenantId)->first();
            if ($tenantData) {
                DB::connection('tenant')->table('identitas_tokos')->insert([
                    'nama_toko' => $tenantData->name,
                    'alamat_toko' => $this->storeAddress,
                    'nomor_telepon' => $this->whatsappNumber,
                    'jenis_layanan' => $this->jenisLayanan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 5. Mark tenant as provisioned
            TenantManager::switchToLandlord();
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                // We use is_active flag as ready state since it was initially false
                $tenant->update(['is_active' => true]);
                
                // Save temp password in cache for provisioning view
                \Illuminate\Support\Facades\Cache::put('tenant_provision_password_' . $this->tenantId, $tempPassword, now()->addMinutes(60));
            }

        } catch (\Exception $e) {
            Log::error("Provisioning Tenant Database Failed: " . $e->getMessage());
            
            // Clean up
            try {
                DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `{$this->dbName}`");
            } catch (\Exception $cleanupEx) {
                // Ignore
            }

            // Mark tenant as failed
            TenantManager::switchToLandlord();
            $tenant = Tenant::find($this->tenantId);
            if ($tenant) {
                $tenant->delete(); // Soft delete or hard delete depending on setup
            }

            throw $e;
        }
    }
}
