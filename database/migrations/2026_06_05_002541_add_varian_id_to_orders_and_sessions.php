<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVarianIdToOrdersAndSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->foreignId('produk_varian_id')->nullable()->after('produk_id')->constrained('produk_varians')->nullOnDelete();
        });

        Schema::table('chatbot_order_sessions', function (Blueprint $table) {
            $table->foreignId('produk_varian_id')->nullable()->after('produk_id')->constrained('produk_varians')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesanan_items', function (Blueprint $table) {
            $table->dropForeign(['produk_varian_id']);
            $table->dropColumn('produk_varian_id');
        });

        Schema::table('chatbot_order_sessions', function (Blueprint $table) {
            $table->dropForeign(['produk_varian_id']);
            $table->dropColumn('produk_varian_id');
        });
    }
}
