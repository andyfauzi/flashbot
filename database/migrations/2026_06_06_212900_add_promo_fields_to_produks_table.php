<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromoFieldsToProduksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->integer('promo_min_qty')->nullable()->after('aktif');
            $table->decimal('promo_harga', 12, 2)->nullable()->after('promo_min_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['promo_min_qty', 'promo_harga']);
        });
    }
}
