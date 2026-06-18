<?php
/**
 * Script darurat: Pastikan semua database tenant lengkap (migrasi ulang yang hilang)
 * Jalankan dengan: php fix_tenant_db.php (dari folder flashbot)
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

echo "=== Fix & Cek Tenant Database ===\n";

$tenants = DB::connection('landlord')->table('tenants')->get(['id','name','subdomain','database_name','is_active']);
$sessionToFind = 'teta-h2js6';
$deviceFoundInTenant = null;

foreach ($tenants as $tenant) {
    echo "\n--- Tenant: {$tenant->name} | subdomain: {$tenant->subdomain} | DB: {$tenant->database_name} | Active: {$tenant->is_active} ---\n";

    $exists = DB::connection('landlord')->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$tenant->database_name]);

    if (empty($exists)) {
        echo "  ⚠️  Database TIDAK ADA! Membuat...\n";
        try {
            DB::connection('landlord')->statement("CREATE DATABASE `{$tenant->database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "  ✅ Database dibuat!\n";
        } catch (\Exception $e) {
            echo "  ❌ Gagal buat database: " . $e->getMessage() . "\n";
            continue;
        }
    }

    // Jalankan/lanjutkan migrasi (aman dijalankan berulang)
    echo "  🔄 Menjalankan migrasi...\n";
    try {
        config(['database.connections.tenant.database' => $tenant->database_name]);
        DB::purge('tenant');
        $exitCode = Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations',
            '--force' => true,
        ]);
        $output = Artisan::output();
        echo "  ✅ Migrasi selesai (exit: {$exitCode})\n";
        if (trim($output)) {
            echo "  📝 " . str_replace("\n", "\n  📝 ", trim($output)) . "\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ Error migrasi: " . $e->getMessage() . "\n";
    }

    config(['database.connections.tenant.database' => $tenant->database_name]);
    DB::purge('tenant');

    // Identitas Toko
    try {
        $identitas = DB::connection('tenant')->table('identitas_tokos')->first();
        echo "  🏪 Identitas Toko: " . ($identitas->nama_toko ?? 'BELUM DIISI') . "\n";
    } catch (\Exception $e) { echo "  🏪 Error: " . $e->getMessage() . "\n"; }

    // Devices
    try {
        $devices = DB::connection('tenant')->table('chatbot_devices')->get(['id','session_id','nomor','status','is_default']);
        echo "  📱 Devices: " . count($devices) . "\n";
        foreach ($devices as $d) {
            $isTarget = ($d->session_id == $sessionToFind) ? ' ← INI' : '';
            echo "     - session_id:{$d->session_id} | nomor:{$d->nomor} | status:{$d->status} | default:{$d->is_default}{$isTarget}\n";
            if ($d->session_id == $sessionToFind) {
                $deviceFoundInTenant = $tenant;
            }
        }
    } catch (\Exception $e) { echo "  📱 Error: " . $e->getMessage() . "\n"; }

    // Menu count
    try {
        $mc = DB::connection('tenant')->table('chatbot_menus')->count();
        echo "  📋 Jumlah Menu: {$mc}\n";
    } catch (\Exception $e) { echo "  📋 Error: " . $e->getMessage() . "\n"; }
}

echo "\n=== RINGKASAN ===\n";
if ($deviceFoundInTenant) {
    echo "✅ Device '{$sessionToFind}' ditemukan di tenant: {$deviceFoundInTenant->name} ({$deviceFoundInTenant->subdomain})\n";
} else {
    echo "❌ Device '{$sessionToFind}' TIDAK DITEMUKAN di tenant manapun!\n";
    echo "   Mendaftarkan device ke tenant 'afracoffe'...\n";
    $afra = DB::connection('landlord')->table('tenants')->where('subdomain', 'afracoffe')->first();
    if ($afra) {
        config(['database.connections.tenant.database' => $afra->database_name]);
        DB::purge('tenant');
        $existingInAfra = DB::connection('tenant')->table('chatbot_devices')
            ->where('session_id', $sessionToFind)->first();
        if (!$existingInAfra) {
            DB::connection('tenant')->table('chatbot_devices')->insert([
                'nama_device'  => 'WhatsApp Bot',
                'nomor'        => '0',
                'session_id'   => $sessionToFind,
                'status'       => 'connected',
                'is_default'   => true,
                'pesan_sapaan' => null,
                'menu_type'    => 'text',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            echo "   ✅ Device berhasil didaftarkan ke afracoffe!\n";
        } else {
            echo "   ℹ️  Device sudah ada di afracoffe.\n";
        }
    }
}

echo "\n=== Selesai ===\n";
