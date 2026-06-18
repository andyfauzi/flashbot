<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKategorisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // Masukkan kategori default
        $kategoriUmumId = DB::table('kategoris')->insertGetId([
            'nama' => 'Umum',
            'deskripsi' => 'Kategori default untuk semua produk lama',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Schema::table('produks', function (Blueprint $table) use ($kategoriUmumId) {
            $table->foreignId('kategori_id')->default($kategoriUmumId)->constrained('kategoris')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });
        Schema::dropIfExists('kategoris');
    }
}
