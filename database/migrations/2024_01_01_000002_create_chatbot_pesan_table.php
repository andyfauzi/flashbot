<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotPesanTable extends Migration
{
    public function up()
    {
        Schema::create('chatbot_pesan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 100)->comment('Nomor WA user');
            $table->enum('arah', ['masuk', 'keluar'])->comment('masuk=dari user, keluar=dari bot');
            $table->text('isi')->comment('Isi pesan');
            $table->timestamp('waktu')->useCurrent();

            $table->index('nomor');
            $table->index('waktu');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_pesan');
    }
}
