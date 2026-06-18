<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateChatbotMenuTable extends Migration
{
    public function up()
    {
        Schema::create('chatbot_menu', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique()->comment('Kode pilihan, misal: 1, 2, 3');
            $table->string('judul', 100)->comment('Judul menu');
            $table->text('isi')->comment('Isi balasan menu');
            $table->boolean('aktif')->default(true);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Data menu default
        DB::table('chatbot_menu')->insert([
            ['kode' => '1', 'judul' => 'Info Produk',     'isi' => "📦 *Info Produk*\n\nKami menyediakan berbagai produk berkualitas.\nKunjungi: www.contoh.com\n\nKetik *0* untuk kembali.", 'aktif' => true, 'urutan' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => '2', 'judul' => 'Cara Pemesanan',  'isi' => "🛒 *Cara Pemesanan*\n\n1. Pilih produk\n2. Hubungi CS\n3. Transfer pembayaran\n4. Produk dikirim\n\nKetik *0* untuk kembali.", 'aktif' => true, 'urutan' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => '3', 'judul' => 'Status Pesanan',  'isi' => "📬 *Status Pesanan*\n\nKirimkan nomor order kamu.\nContoh: ORDER-12345\n\nKetik *0* untuk kembali.", 'aktif' => true, 'urutan' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['kode' => '4', 'judul' => 'Hubungi CS',      'isi' => "👩‍💼 *Hubungi CS*\n\n📞 WA: 0812-3456-7890\n📧 Email: cs@contoh.com\n⏰ Senin-Jumat 08.00-17.00\n\nKetik *0* untuk kembali.", 'aktif' => true, 'urutan' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_menu');
    }
}
