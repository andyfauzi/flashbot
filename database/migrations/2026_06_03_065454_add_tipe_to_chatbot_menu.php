<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipeToChatbotMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_menu', function (Blueprint $table) {
            $table->enum('tipe_pesan', ['text', 'button', 'list'])->default('text')->after('isi');
            $table->json('pilihan_interaktif')->nullable()->after('tipe_pesan');
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
            $table->dropColumn(['tipe_pesan', 'pilihan_interaktif']);
        });
    }
}
