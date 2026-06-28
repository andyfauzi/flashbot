<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running migrations for Landlord...\n";
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
echo \Illuminate\Support\Facades\Artisan::output();

$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    echo "Checking Tenant ID: {$tenant->id}...\n";
    \App\Services\TenantManager::switchToTenant($tenant);
    try {
        $res = \Illuminate\Support\Facades\DB::select('DESCRIBE resep_addons');
        print_r($res);
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
echo "Done.\n";
