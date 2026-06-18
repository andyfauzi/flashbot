<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupTables extends Migration
{
    public function up()
    {
        // Tabel simpan semua pesan grup
        Schema::create('grup_pesan', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id')->comment('ID grup WA');
            $table->string('grup_nama')->nullable()->comment('Nama grup');
            $table->string('pengirim')->comment('Nomor pengirim');
            $table->string('nama_pengirim')->nullable()->comment('Nama pengirim');
            $table->text('pesan')->comment('Isi pesan');
            $table->timestamp('waktu')->useCurrent();

            $table->index('grup_id');
            $table->index('pengirim');
            $table->index('waktu');
        });

        // Tabel catatan penting grup
        Schema::create('grup_catatan', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id')->comment('ID grup WA');
            $table->string('disimpan_oleh')->comment('Nomor yang menyimpan');
            $table->text('isi')->comment('Isi catatan');
            $table->string('tag')->nullable()->comment('Tag/kategori catatan');
            $table->timestamp('waktu')->useCurrent();

            $table->index('grup_id');
        });

        // Tabel pengingat grup
        Schema::create('grup_pengingat', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id')->comment('ID grup WA');
            $table->string('dibuat_oleh')->comment('Nomor yang membuat reminder');
            $table->text('pesan')->comment('Isi pengingat');
            $table->timestamp('waktu_ingatkan')->comment('Kapan bot harus mengingatkan');
            $table->boolean('sudah_dikirim')->default(false);
            $table->timestamps();

            $table->index('waktu_ingatkan');
            $table->index('sudah_dikirim');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grup_pengingat');
        Schema::dropIfExists('grup_catatan');
        Schema::dropIfExists('grup_pesan');
    }
}
