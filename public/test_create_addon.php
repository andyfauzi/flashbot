<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::first();
if ($tenant) {
    \App\Services\TenantManager::switchToTenant($tenant);
    
    $produk = \App\Models\Produk::first();
    if ($produk) {
        $addonData = [
            'nama_addon' => 'Test Addon',
            'harga' => 5000,
            'butuh_teks' => false
        ];
        
        try {
            $newAddon = $produk->addons()->create([
                'nama_addon' => $addonData['nama_addon'],
                'harga' => $addonData['harga'],
                'butuh_teks' => $addonData['butuh_teks']
            ]);
            echo "Created Addon ID: " . $newAddon->id . "\n";
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No products found.\n";
    }
}
