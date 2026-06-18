<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTanggalDiambilToChatbotOrderSessions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_order_sessions', function (Blueprint $table) {
            $table->date('tanggal_diambil')->nullable()->after('produk_varian_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatbot_order_sessions', function (Blueprint $table) {
            $table->dropColumn('tanggal_diambil');
        });
    }
}
