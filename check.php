<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $users = \Illuminate\Support\Facades\DB::connection('mysql')->select("SELECT * FROM tenant_ninsky.users");
    print_r($users);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
