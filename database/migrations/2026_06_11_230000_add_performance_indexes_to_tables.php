<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to pesanans table
        Schema::table('pesanans', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });

        // Add indexes to cash_flows table
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('tipe');
        });

        // Add indexes to stok_bahan_histories table
        Schema::table('stok_bahan_histories', function (Blueprint $table) {
            $table->index('bahan_baku_id');
            $table->index('created_at');
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
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('cash_flows', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['tipe']);
        });

        Schema::table('stok_bahan_histories', function (Blueprint $table) {
            $table->dropIndex(['bahan_baku_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
