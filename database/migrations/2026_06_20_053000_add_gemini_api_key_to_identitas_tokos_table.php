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
            if (!Schema::hasColumn('identitas_tokos', 'gemini_api_key')) {
                $table->string('gemini_api_key')->nullable();
            }
            if (!Schema::hasColumn('identitas_tokos', 'whatsapp_gateway')) {
                $table->string('whatsapp_gateway')->default('baileys');
            }
            if (!Schema::hasColumn('identitas_tokos', 'meta_phone_number_id')) {
                $table->string('meta_phone_number_id')->nullable();
            }
            if (!Schema::hasColumn('identitas_tokos', 'meta_access_token')) {
                $table->text('meta_access_token')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn(['gemini_api_key', 'whatsapp_gateway', 'meta_phone_number_id', 'meta_access_token']);
        });
    }
};
