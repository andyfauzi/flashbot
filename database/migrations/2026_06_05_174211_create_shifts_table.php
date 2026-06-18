<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->datetime('waktu_buka');
            $table->datetime('waktu_tutup')->nullable();
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->decimal('pengeluaran_kasir', 15, 2)->default(0);
            $table->decimal('total_penjualan_tunai', 15, 2)->default(0);
            $table->decimal('selisih_uang', 15, 2)->default(0);
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
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
        Schema::dropIfExists('shifts');
    }
}
