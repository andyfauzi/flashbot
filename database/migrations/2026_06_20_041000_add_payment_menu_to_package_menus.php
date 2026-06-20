<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert new menu to package_menus in landlord db
        DB::connection('landlord')->table('package_menus')->insert([
            'menu_key' => 'pengaturan_pembayaran',
            'menu_label' => 'Pengaturan Payment Gateway',
            'kategori' => 'Pengaturan Sistem',
            'gratis_enabled' => false,
            'starter_enabled' => false,
            'pro_enabled' => true,
            'business_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('landlord')->table('package_menus')
            ->where('menu_key', 'pengaturan_pembayaran')
            ->delete();
    }
};
