<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = \App\Models\Tenant::first();
if ($tenant) {
    \App\Services\TenantManager::switchToTenant($tenant);
    $produk = \App\Models\Produk::with(['varians', 'addons', 'bundleItems'])->find(2);
    
    $kategoris = \App\Models\KategoriProduk::all();
    $allVarians = \App\Models\ProdukVarian::with('produk')->get();
    
    // Simulate View Rendering
    try {
        $html = view('chatbot.produk.form', compact('produk', 'kategoris', 'allVarians'))->render();
        echo "Render Success. Length: " . strlen($html) . "\n";
        
        // Find JS variables
        preg_match_all('/let (addonIndex|varianIndex|bundleIndex) = .*;/i', $html, $matches);
        print_r($matches[0]);
    } catch (\Exception $e) {
        echo "Render Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    } catch (\Throwable $e) {
        echo "Fatal Error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
