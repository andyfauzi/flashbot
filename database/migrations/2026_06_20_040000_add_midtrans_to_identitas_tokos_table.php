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
            $table->string('midtrans_server_key')->nullable()->after('xendit_webhook_token');
            $table->string('midtrans_client_key')->nullable()->after('midtrans_server_key');
            $table->boolean('midtrans_is_production')->default(false)->after('midtrans_client_key');
            $table->boolean('is_midtrans_active')->default(false)->after('midtrans_is_production');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_server_key',
                'midtrans_client_key',
                'midtrans_is_production',
                'is_midtrans_active'
            ]);
        });
    }
};
