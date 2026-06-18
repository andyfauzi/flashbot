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
        Schema::create('chatbot_order_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_wa', 50)->unique();
            $table->string('langkah', 50)->default('pilih_produk');
            $table->foreignId('produk_id')->nullable()->constrained('produks')->onDelete('set null');
            $table->integer('jumlah')->default(0);
            $table->string('nama_penerima', 100)->nullable();
            $table->text('alamat_penerima')->nullable();
            $table->string('tipe_pengiriman', 50)->nullable(); // 'diantar' atau 'ambil_sendiri'
            $table->string('metode_pembayaran', 50)->nullable();
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
        Schema::dropIfExists('chatbot_order_sessions');
    }
};
