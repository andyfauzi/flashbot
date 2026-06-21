<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Karena ini multi-tenant, kita harus inisialisasi tenant agar model bisa konek ke DB yang benar
// Coba list semua tenant
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    echo "Tenant: " . $tenant->name . " (ID: " . $tenant->id . ")\n";
    try {
        app('App\Services\TenantManager')->setTenant($tenant);
        $pesanans = \App\Models\Pesanan::latest()->take(3)->get(['nomor_order', 'nomor_antrian', 'created_at']);
        foreach ($pesanans as $p) {
            echo "  - " . $p->nomor_order . " | Antrian: " . var_export($p->nomor_antrian, true) . " | Date: " . $p->created_at . "\n";
        }
    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}
