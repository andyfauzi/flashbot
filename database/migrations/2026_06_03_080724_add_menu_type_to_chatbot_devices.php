<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_devices', function (Blueprint $table) {
            // Tipe tampilan menu pertama: text, button, atau list
            $table->string('menu_type', 10)->default('text')->after('pesan_sapaan');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_devices', function (Blueprint $table) {
            $table->dropColumn('menu_type');
        });
    }
};
