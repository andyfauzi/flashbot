<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('nama_device');
            $table->string('nomor')->nullable();
            $table->string('session_id')->unique();
            $table->enum('status', ['connected', 'disconnected', 'qr'])->default('disconnected');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatbot_devices');
    }
}
