<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotUsersTable extends Migration
{
    public function up()
    {
        Schema::create('chatbot_users', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 100)->unique()->comment('Nomor WA user');
            $table->string('nama', 100)->nullable()->comment('Nama user');
            $table->string('langkah', 50)->default('menu')->comment('Sesi terakhir');
            $table->timestamp('pertama_chat')->useCurrent();
            $table->timestamp('terakhir_chat')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_users');
    }
}
