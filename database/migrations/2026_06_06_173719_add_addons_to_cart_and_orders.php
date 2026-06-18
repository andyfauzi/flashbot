<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddonsToCartAndOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_cart_items', function (Blueprint $table) {
            $table->json('addons')->nullable()->after('produk_varian_id');
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->json('addons')->nullable()->after('produk_varian_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatbot_cart_items', function (Blueprint $table) {
            $table->dropColumn('addons');
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->dropColumn('addons');
        });
    }
}
