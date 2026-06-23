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
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->string('hero_image_path')->nullable();
            $table->text('deskripsi_toko')->nullable();
            $table->json('galeri_paths')->nullable();
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
            $table->dropColumn(['hero_image_path', 'deskripsi_toko', 'galeri_paths']);
        });
    }
};
