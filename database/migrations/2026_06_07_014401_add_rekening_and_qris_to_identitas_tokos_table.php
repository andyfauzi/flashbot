<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRekeningAndQrisToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->text('nomor_rekening')->nullable();
            $table->string('qris_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn(['nomor_rekening', 'qris_path']);
        });
    }
}
