<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EnforceSubscriptions extends Command
{
    protected $signature = 'flashbot:check-subscriptions';
    protected $description = 'Memeriksa dan menonaktifkan tenant yang langganannya melewati masa tenggang (grace period).';

    public function handle()
    {
        $this->info('Starting subscription enforcement check...');
        TenantManager::switchToLandlord();

        // 3 days grace period
        $graceDate = now()->subDays(3);

        $expiredTenants = Tenant::where('is_active', true)
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<', $graceDate)
            ->get();

        $count = 0;
        foreach ($expiredTenants as $tenant) {
            $tenant->update(['is_active' => false]);
            $count++;
            $this->line("Suspended tenant ID {$tenant->id} ({$tenant->subdomain}) - expired since {$tenant->plan_expires_at}");
            Log::info("Tenant suspended due to expired subscription: {$tenant->id} - {$tenant->subdomain}");
        }

        $this->info("Completed. Suspended {$count} tenants.");
        return 0;
    }
}
