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
        Schema::create('bahan_bakus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bahan');
            $table->string('satuan')->default('gram'); // gram, ml, pcs
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('qty_beli', 15, 2);
            $table->decimal('harga_per_unit', 15, 2); // harga_beli / qty_beli
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
