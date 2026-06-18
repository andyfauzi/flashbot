<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resep_varians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_varian_id')->constrained('produk_varians')->onDelete('cascade');
            $table->foreignId('bahan_baku_id')->constrained('bahan_bakus')->onDelete('cascade');
            $table->decimal('qty_dipakai', 15, 2); // Kuantitas bahan yang digunakan untuk varian ini
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep_varians');
    }
};
