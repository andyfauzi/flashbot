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
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_order', 50)->unique();
            $table->string('nomor_wa', 50);
            $table->string('nama_penerima', 100);
            $table->text('alamat_penerima');
            $table->string('tipe_pengiriman', 50)->default('diantar'); // 'diantar' atau 'ambil_sendiri'
            $table->decimal('biaya_barang', 12, 2)->default(0);
            $table->decimal('biaya_pengantaran', 12, 2)->default(0);
            $table->decimal('total_biaya', 12, 2)->default(0);
            $table->string('metode_pembayaran', 50)->nullable(); // 'qris', 'transfer', 'cod'
            $table->string('status', 30)->default('pending_ongkir'); // pending_ongkir, pending_payment, pending, pending_approval, paid, approved, completed, cancelled
            $table->string('bukti_pembayaran')->nullable();
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
        Schema::dropIfExists('pesanans');
    }
};
