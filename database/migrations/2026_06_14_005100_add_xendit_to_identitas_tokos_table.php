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
            $table->string('xendit_api_key')->nullable()->after('qris_path');
            $table->string('xendit_webhook_token')->nullable()->after('xendit_api_key');
            $table->boolean('is_payment_gateway_active')->default(false)->after('xendit_webhook_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn(['xendit_api_key', 'xendit_webhook_token', 'is_payment_gateway_active']);
        });
    }
};
