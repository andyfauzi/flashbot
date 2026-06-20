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
        if (Schema::connection('landlord')->hasTable('sales_vouchers')) {
            if (!Schema::connection('landlord')->hasColumn('sales_vouchers', 'target_paket')) {
                Schema::connection('landlord')->table('sales_vouchers', function (Blueprint $table) {
                    $table->string('target_paket')->default('semua')->after('komisi_persen'); // semua, starter, pro, business
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('landlord')->hasTable('sales_vouchers')) {
            if (Schema::connection('landlord')->hasColumn('sales_vouchers', 'target_paket')) {
                Schema::connection('landlord')->table('sales_vouchers', function (Blueprint $table) {
                    $table->dropColumn('target_paket');
                });
            }
        }
    }
};
