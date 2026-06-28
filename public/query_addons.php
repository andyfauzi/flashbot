<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProdukAddon;
use App\Models\ProdukVarian;
use App\Models\Produk;

$addons = DB::table('produk_addons')->get();
echo "Addons in landlord: " . count($addons) . "\n";

$tenant = \App\Models\Tenant::first();
if ($tenant) {
    \App\Services\TenantManager::switchToTenant($tenant);
    $addons = ProdukAddon::all();
    echo "Addons in tenant " . $tenant->name . ": " . count($addons) . "\n";
    foreach($addons as $a) {
        echo "- {$a->nama_addon} (Rp {$a->harga}) for produk_id {$a->produk_id}\n";
    }
}
