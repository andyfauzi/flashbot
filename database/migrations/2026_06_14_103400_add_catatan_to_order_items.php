<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCatatanToOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('chatbot_cart_items', 'catatan')) {
                $table->string('catatan', 500)->nullable()->after('addons');
            }
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan_items', 'catatan')) {
                $table->string('catatan', 500)->nullable()->after('addons');
            }
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
            if (Schema::hasColumn('chatbot_cart_items', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            if (Schema::hasColumn('pesanan_items', 'catatan')) {
                $table->dropColumn('catatan');
            }
        });
    }
}
