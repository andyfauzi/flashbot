<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterKodeOnChatbotMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_menu', function (Blueprint $table) {
            // Karena ini SQLite di beberapa environment Laravel, drop unique bisa rumit, tapi di MySQL gampang.
            // Kita drop constraint unique lama. Namanya biasanya 'chatbot_menu_kode_unique'
            try {
                $table->dropUnique('chatbot_menu_kode_unique');
            } catch (\Exception $e) {
                // Ignore jika index tidak ada
            }

            // Ubah tipe data kode menjadi string(255) dengan raw SQL untuk menghindari DBAL
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE chatbot_menu MODIFY kode VARCHAR(255) NOT NULL');

            // Tambahkan unique index kombinasi device_id dan kode
            // Abaikan jika table engine tidak support atau sudah ada
            try {
                $table->unique(['device_id', 'kode'], 'chatbot_menu_device_kode_unique');
            } catch (\Exception $e) {
                // Ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatbot_menu', function (Blueprint $table) {
            try {
                $table->dropUnique('chatbot_menu_device_kode_unique');
            } catch (\Exception $e) {
                // Ignore
            }

            \Illuminate\Support\Facades\DB::statement('ALTER TABLE chatbot_menu MODIFY kode VARCHAR(10) NOT NULL');

            try {
                $table->unique('kode', 'chatbot_menu_kode_unique');
            } catch (\Exception $e) {
                // Ignore
            }
        });
    }
}
