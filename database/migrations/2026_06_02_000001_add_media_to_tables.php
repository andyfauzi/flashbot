<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMediaToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Tambahkan kolom media_url dan media_type ke tabel chatbot_pesan
        Schema::table('chatbot_pesan', function (Blueprint $table) {
            $table->string('media_url', 500)->nullable()->after('isi')->comment('URL media atau gambar');
            $table->string('media_type', 50)->nullable()->after('media_url')->comment('Tipe media: image, document, dll');
        });

        // Tambahkan kolom media_url dan media_type ke tabel chatbot_menu
        Schema::table('chatbot_menu', function (Blueprint $table) {
            $table->string('media_url', 500)->nullable()->after('isi')->comment('URL media untuk balasan menu');
            $table->string('media_type', 50)->nullable()->after('media_url')->comment('Tipe media untuk balasan menu');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatbot_pesan', function (Blueprint $table) {
            $table->dropColumn(['media_url', 'media_type']);
        });

        Schema::table('chatbot_menu', function (Blueprint $table) {
            $table->dropColumn(['media_url', 'media_type']);
        });
    }
}
