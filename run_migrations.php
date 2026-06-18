<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Models\Tenant;

$tenants = Tenant::all();
foreach($tenants as $tenant) {
    echo "Migrating {$tenant->database_name}...\n";
    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');
    try {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
        echo Artisan::output() . "\n";
    } catch (\Exception $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}
echo "Done!\n";
