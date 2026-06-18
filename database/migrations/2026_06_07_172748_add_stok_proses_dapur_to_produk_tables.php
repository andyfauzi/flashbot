<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStokProsesDapurToProdukTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->integer('stok_proses_dapur')->default(0)->after('stok');
        });
        Schema::table('produk_varians', function (Blueprint $table) {
            $table->integer('stok_proses_dapur')->default(0)->after('stok');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('stok_proses_dapur');
        });
        Schema::table('produk_varians', function (Blueprint $table) {
            $table->dropColumn('stok_proses_dapur');
        });
    }
}
