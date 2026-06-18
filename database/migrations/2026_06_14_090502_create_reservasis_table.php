<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservasisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->nullable()->constrained('mejas')->nullOnDelete();
            $table->string('nama_pelanggan');
            $table->string('nomor_telepon');
            $table->dateTime('tanggal_waktu');
            $table->integer('jumlah_orang');
            $table->text('catatan')->nullable();
            
            // Kolom terkait DP
            $table->boolean('is_dp_required')->default(false);
            $table->decimal('nominal_dp', 15, 2)->default(0);
            $table->enum('status_pembayaran_dp', ['belum_bayar', 'lunas'])->default('belum_bayar');

            $table->enum('status', ['menunggu', 'dikonfirmasi', 'selesai', 'batal'])->default('menunggu');
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
        Schema::dropIfExists('reservasis');
    }
}
