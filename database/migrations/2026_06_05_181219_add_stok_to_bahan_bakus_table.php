<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStokToBahanBakusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->decimal('stok', 10, 2)->default(0)->after('qty_beli');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('stok');
        });
    }
}
