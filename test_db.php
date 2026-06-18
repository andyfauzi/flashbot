<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Grup Admin ---\n";
$admins = \App\Models\GrupAdmin::get();
print_r($admins->toArray());
