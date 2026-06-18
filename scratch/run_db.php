<?php

// Jalankan dari document root laravel: php scratch/run_db.php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Landlord
    DB::connection('landlord')->table('landlord_settings')->insertOrIgnore([
        'key' => 'is_payment_gateway_enabled',
        'value' => '1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Landlord settings updated.\n";

    // Tenants
    $tenants = \App\Models\Tenant::all();
    foreach ($tenants as $tenant) {
        \App\Services\TenantManager::switchTo($tenant);
        if (!Schema::hasColumn('identitas_tokos', 'xendit_api_key')) {
            Schema::table('identitas_tokos', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('xendit_api_key')->nullable()->after('qris_path');
                $table->string('xendit_webhook_token')->nullable()->after('xendit_api_key');
                $table->boolean('is_payment_gateway_active')->default(false)->after('xendit_webhook_token');
            });
            echo "Tenant {$tenant->id} updated.\n";
        } else {
            echo "Tenant {$tenant->id} already has xendit columns.\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
