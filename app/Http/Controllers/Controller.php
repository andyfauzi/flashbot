<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function requireTenant(): \App\Models\Tenant
    {
        if (!app()->bound('current_tenant')) {
            abort(404, 'Toko tidak ditemukan atau tidak aktif.');
        }
        
        $tenant = app('current_tenant');
        
        if (!$tenant->is_active) {
            abort(403, 'Langganan toko ini tidak aktif. Hubungi pemilik toko.');
        }
        
        return $tenant;
    }
}
