<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPreorderFieldsToPesanans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->date('tanggal_diambil')->nullable()->after('metode_pembayaran');
            $table->decimal('uang_muka', 12, 2)->default(0)->after('total_biaya');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn(['tanggal_diambil', 'uang_muka']);
        });
    }
}
