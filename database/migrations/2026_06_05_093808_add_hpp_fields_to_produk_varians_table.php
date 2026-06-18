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
        Schema::table('produk_varians', function (Blueprint $table) {
            $table->decimal('harga', 15, 2)->nullable(); // Overrides produk.harga if set
            $table->decimal('hpp', 15, 2)->default(0); // Cost of ingredients
            $table->decimal('overhead_cost', 15, 2)->default(0); // Gas, kemasan, etc
            $table->decimal('harga_kompetitor', 15, 2)->nullable();
            $table->decimal('target_margin', 5, 2)->default(0); // Percentage, e.g., 40.00
            $table->decimal('harga_rekomendasi', 15, 2)->default(0); // Auto-calculated based on HPP + Margin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produk_varians', function (Blueprint $table) {
            $table->dropColumn([
                'harga',
                'hpp',
                'overhead_cost',
                'harga_kompetitor',
                'target_margin',
                'harga_rekomendasi'
            ]);
        });
    }
};
