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
        if (!Schema::connection('landlord')->hasTable('landlord_expenses')) {
            Schema::connection('landlord')->create('landlord_expenses', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->string('nama_pengeluaran');
                $table->string('kategori')->nullable();
                $table->decimal('nominal', 15, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('landlord_expenses');
    }
};
