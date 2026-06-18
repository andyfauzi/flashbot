<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_menu', function (Blueprint $table) {
            // parent_kode: kode menu induk. Jika NULL = menu top-level.
            // Jika diisi misal "1" = sub-menu dari menu berkode "1"
            $table->string('parent_kode', 100)->nullable()->after('kode');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_menu', function (Blueprint $table) {
            $table->dropColumn('parent_kode');
        });
    }
};
