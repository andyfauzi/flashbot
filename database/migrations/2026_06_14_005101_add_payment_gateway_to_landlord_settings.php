<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a landlord setting to enable/disable payment gateway feature globally
        DB::connection('landlord')->table('landlord_settings')->insertOrIgnore([
            'key' => 'is_payment_gateway_enabled',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::connection('landlord')->table('landlord_settings')
            ->where('key', 'is_payment_gateway_enabled')
            ->delete();
    }
};
