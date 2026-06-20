<?php

namespace App\Helpers;

use App\Models\PackageMenu;
use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantPlanHelper
{
    /**
     * Mengecek apakah menu tertentu diizinkan untuk paket tenant saat ini.
     * Jika tenant memiliki role Super Admin atau tidak ada tenant aktif, kembalikan true (atau false sesuai konteks).
     */
    public static function hasMenu($menuKey)
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        // Jika tidak ada konteks tenant (misal lagi di halaman landlord), izinkan saja
        if (!$tenant) {
            return true;
        }

        $plan = strtolower($tenant->plan ?? 'gratis');
        
        // Cache selama 24 jam (1440 menit) per paket
        $menus = Cache::remember('plan_menus_' . $plan, 1440, function () use ($plan) {
            $column = $plan . '_enabled';
            // Validasi kolom agar tidak error SQL Injection
            if (!in_array($column, ['gratis_enabled', 'starter_enabled', 'pro_enabled', 'business_enabled'])) {
                $column = 'gratis_enabled';
            }
            
            return PackageMenu::where($column, true)->pluck('menu_key')->toArray();
        });

        return in_array($menuKey, $menus);
    }
}
