<?php
/**
 * Script: Drop semua tabel di tenant_afracoffe lalu migrasi ulang dari nol
 * Aman digunakan karena database ini baru dibuat & belum ada data penting
 * Jalankan: php force_migrate_tenant.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

$targetSubdomain = 'afracoffe';
echo "=== Force Migrate Tenant: {$targetSubdomain} ===\n";

$tenant = DB::connection('landlord')->table('tenants')->where('subdomain', $targetSubdomain)->first();
if (!$tenant) {
    echo "❌ Tenant '{$targetSubdomain}' tidak ditemukan!\n";
    exit(1);
}

echo "✅ Tenant: {$tenant->name} | DB: {$tenant->database_name}\n";

// Setup koneksi
config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');

// Step 1: Disable foreign key checks dulu
DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');

// Step 2: Ambil semua tabel yang ada
$tables = DB::connection('tenant')->select('SHOW TABLES');
$existingTables = array_map(fn($t) => array_values((array)$t)[0], $tables);

echo "\n🗑️  Menghapus " . count($existingTables) . " tabel...\n";
foreach ($existingTables as $table) {
    DB::connection('tenant')->statement("DROP TABLE IF EXISTS `{$table}`");
    echo "   - Dropped: {$table}\n";
}

// Re-enable foreign key checks
DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
echo "✅ Semua tabel dihapus!\n";

// Step 3: Jalankan semua migrasi dari awal
echo "\n🔄 Menjalankan migrasi dari awal...\n";
try {
    $exitCode = Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path'     => 'database/migrations',
        '--force'    => true,
    ]);
    $output = Artisan::output();
    echo "✅ Migrasi selesai (exit: {$exitCode})\n";
    // Print migrasi yang dijalankan
    foreach (explode("\n", trim($output)) as $line) {
        if (trim($line)) echo "   {$line}\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 4: Verifikasi tabel yang diperlukan
echo "\n📋 Verifikasi tabel penting:\n";
$required = ['users','chatbot_menus','chatbot_devices','chatbot_users','chatbot_pesan',
             'produks','pesanans','identitas_tokos','jobs','chatbot_histories'];
$tablesNow = array_map(
    fn($t) => array_values((array)$t)[0],
    DB::connection('tenant')->select('SHOW TABLES')
);
foreach ($required as $t) {
    $status = in_array($t, $tablesNow) ? '✅' : '❌';
    echo "   {$status} {$t}\n";
}

// Step 5: Daftarkan device kembali
echo "\n📱 Mendaftarkan device teta-h2js6...\n";
$device = DB::connection('tenant')->table('chatbot_devices')->where('session_id', 'teta-h2js6')->first();
if (!$device) {
    DB::connection('tenant')->table('chatbot_devices')->insert([
        'nama_device'  => 'WhatsApp Bot',
        'nomor'        => '0',
        'session_id'   => 'teta-h2js6',
        'status'       => 'connected',
        'is_default'   => true,
        'pesan_sapaan' => null,
        'menu_type'    => 'text',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);
    echo "✅ Device didaftarkan!\n";
} else {
    echo "✅ Device sudah ada.\n";
}

echo "\n=== SELESAI! ===\n";
echo "Silakan jalankan: php artisan cache:clear\n";
echo "Lalu buka: http://afracoffe.localhost:8001/login\n";
