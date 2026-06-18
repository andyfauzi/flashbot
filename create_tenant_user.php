<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$targetSubdomain = 'afracoffe';

$tenant = DB::connection('landlord')->table('tenants')->where('subdomain', $targetSubdomain)->first();
if (!$tenant) {
    echo "❌ Tenant tidak ditemukan!\n";
    exit(1);
}

// Setup koneksi
config(['database.connections.tenant.database' => $tenant->database_name]);
DB::purge('tenant');

$email = 'admin@afracoffe.com';
$password = 'password123';

$userExists = DB::connection('tenant')->table('users')->where('email', $email)->first();

if (!$userExists) {
    DB::connection('tenant')->table('users')->insert([
        'name' => 'Owner Afra Coffe',
        'email' => $email,
        'password' => Hash::make($password),
        'role' => 'owner',
        'must_change_password' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ User Admin berhasil dibuat!\n";
} else {
    DB::connection('tenant')->table('users')->where('email', $email)->update([
        'password' => Hash::make($password),
        'must_change_password' => false
    ]);
    echo "✅ Password User Admin berhasil direset!\n";
}

// Set Identitas Toko jika kosong
$identitas = DB::connection('tenant')->table('identitas_tokos')->first();
if (!$identitas) {
    DB::connection('tenant')->table('identitas_tokos')->insert([
        'nama_toko' => $tenant->name,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Identitas Toko berhasil diset ke: {$tenant->name}\n";
}

echo "\n==========================\n";
echo "🔐 KREDENSIAL LOGIN\n";
echo "Email    : {$email}\n";
echo "Password : {$password}\n";
echo "==========================\n";
