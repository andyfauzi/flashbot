<?php

use App\Models\Tenant;
use App\Services\TenantManager;
use App\Models\ChatbotMenu;

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = Tenant::where('id', 'afracoffe')->first();
if (!$tenant) {
    echo "Tenant not found.\n";
    exit;
}

TenantManager::switchTo($tenant);

$menus = ChatbotMenu::all();
foreach ($menus as $m) {
    echo "ID: {$m->id} | Judul: {$m->judul} | Kode: {$m->kode} | Aktif: {$m->aktif} | DeviceID: {$m->device_id}\n";
}
