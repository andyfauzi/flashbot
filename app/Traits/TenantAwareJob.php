<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Services\TenantManager;

trait TenantAwareJob
{
    public ?int $tenant_id = null;

    /**
     * Call this inside the constructor of the Job.
     */
    protected function initializeTenantContext()
    {
        $tenant = TenantManager::current();
        if ($tenant) {
            $this->tenant_id = $tenant->id;
        }
    }

    /**
     * Call this at the start of the handle() method.
     */
    protected function restoreTenantContext()
    {
        if ($this->tenant_id) {
            $tenant = Tenant::find($this->tenant_id);
            if ($tenant) {
                TenantManager::switchTo($tenant);
            }
        } else {
            TenantManager::switchToLandlord();
        }
    }

    /**
     * Call this at the end of the handle() method or in a finally block.
     */
    protected function forgetTenantContext()
    {
        TenantManager::forgetTenant();
    }
}
