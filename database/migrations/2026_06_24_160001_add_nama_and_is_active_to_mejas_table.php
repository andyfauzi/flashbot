<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNamaAndIsActiveToMejasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->string('nama_meja')->nullable()->after('nomor_meja');
            $table->string('deskripsi')->nullable()->after('kapasitas');
            $table->boolean('is_active')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mejas', function (Blueprint $table) {
            $table->dropColumn(['nama_meja', 'deskripsi', 'is_active']);
        });
    }
}
