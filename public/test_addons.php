<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::first();
if ($tenant) {
    app()->instance('current_tenant', $tenant);
}

$produk = \App\Models\Produk::withoutGlobalScopes()->with(['varians', 'addons'])
            ->where('aktif', true)
            ->where('nama', 'like', '%Donat%')
            ->first();

if ($produk) {
    echo "Produk:\n";
    echo $produk->toJson();
} else {
    echo "Produk not found";
}
