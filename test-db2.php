<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "DB Dump:\n";
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    $output .= "Tenant: " . $tenant->name . "\n";
    try {
        app('App\Services\TenantManager')->setTenant($tenant);
        $pesanans = \App\Models\Pesanan::latest()->take(5)->get(['nomor_order', 'nomor_antrian', 'created_at']);
        foreach ($pesanans as $p) {
            $output .= "  - " . $p->nomor_order . " | Antrian: " . ($p->nomor_antrian ?? 'NULL') . " | Date: " . $p->created_at . "\n";
        }
    } catch (\Exception $e) {
        $output .= "  Error: " . $e->getMessage() . "\n";
    }
}
file_put_contents(__DIR__.'/dump-pesanans.txt', $output);
echo "Done.";
