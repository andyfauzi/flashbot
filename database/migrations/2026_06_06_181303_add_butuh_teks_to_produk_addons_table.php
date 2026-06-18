<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddButuhTeksToProdukAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('produk_addons', function (Blueprint $table) {
            $table->boolean('butuh_teks')->default(false)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_addons', function (Blueprint $table) {
            $table->dropColumn('butuh_teks');
        });
    }
}
