<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservasiIdToPesanansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->unsignedBigInteger('reservasi_id')->nullable()->after('meja_id');
            // Assuming reservasis table exists
            $table->foreign('reservasi_id')->references('id')->on('reservasis')->onDelete('set null');
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
            $table->dropForeign(['reservasi_id']);
            $table->dropColumn('reservasi_id');
        });
    }
}
