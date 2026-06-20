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
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->string('gemini_api_key')->nullable()->after('whatsapp_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn('gemini_api_key');
        });
    }
};
