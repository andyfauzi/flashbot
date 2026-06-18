<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Validation\ValidationException;

trait EnforcesLimits
{
    /**
     * Define plan limits.
     */
    protected static $planLimits = [
        'starter' => [
            'products' => 50,
            'users' => 3,
        ],
        'pro' => [
            'products' => 500,
            'users' => 10,
        ],
        'business' => [
            'products' => PHP_INT_MAX,
            'users' => PHP_INT_MAX,
        ],
    ];

    /**
     * Hook into the boot method to enforce limits before creating.
     */
    protected static function bootEnforcesLimits()
    {
        static::creating(function ($model) {
            $model->checkLimits();
        });
    }

    /**
     * Check if the tenant has exceeded their limit for this resource type.
     */
    public function checkLimits()
    {
        $tenantId = app('current_tenant')->id ?? null;
        if (!$tenantId) {
            return; // Not in tenant context
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        $plan = $tenant->plan ?? 'starter';
        
        // Determine resource type based on model class
        $resourceType = strtolower(class_basename(static::class)) . 's'; // e.g. 'products'
        
        if (isset(self::$planLimits[$plan][$resourceType])) {
            $limit = self::$planLimits[$plan][$resourceType];
            
            // Count current items
            $currentCount = static::count();
            
            if ($currentCount >= $limit) {
                throw ValidationException::withMessages([
                    'limit_exceeded' => "Anda telah mencapai batas maksimal ({$limit} {$resourceType}) untuk paket {$plan}. Silakan upgrade paket Anda."
                ]);
            }
        }
    }
}
