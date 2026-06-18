<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatbotAiTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_histories', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_wa')->index();
            $table->enum('role', ['user', 'model']);
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('chatbot_carts', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_wa')->unique();
            $table->string('nama_draft')->nullable();
            $table->string('alamat_draft')->nullable();
            $table->date('tanggal_diambil_draft')->nullable();
            $table->timestamps();
        });

        Schema::create('chatbot_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('chatbot_carts')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
            $table->foreignId('produk_varian_id')->nullable()->constrained('produk_varians')->onDelete('cascade');
            $table->integer('jumlah')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_cart_items');
        Schema::dropIfExists('chatbot_carts');
        Schema::dropIfExists('chatbot_histories');
    }
}
