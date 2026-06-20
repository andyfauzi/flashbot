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
        if (Schema::connection('landlord')->hasTable('tenant_payments')) {
            Schema::connection('landlord')->table('tenant_payments', function (Blueprint $table) {
                if (!Schema::connection('landlord')->hasColumn('tenant_payments', 'sales_voucher_id')) {
                    $table->foreignId('sales_voucher_id')->nullable()->constrained('sales_vouchers')->onDelete('set null');
                }
                if (!Schema::connection('landlord')->hasColumn('tenant_payments', 'discount_amount')) {
                    $table->decimal('discount_amount', 12, 2)->default(0);
                }
                if (!Schema::connection('landlord')->hasColumn('tenant_payments', 'commission_amount')) {
                    $table->decimal('commission_amount', 12, 2)->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('landlord')->hasTable('tenant_payments')) {
            Schema::connection('landlord')->table('tenant_payments', function (Blueprint $table) {
                if (Schema::connection('landlord')->hasColumn('tenant_payments', 'sales_voucher_id')) {
                    $table->dropForeign(['sales_voucher_id']);
                    $table->dropColumn('sales_voucher_id');
                }
                if (Schema::connection('landlord')->hasColumn('tenant_payments', 'discount_amount')) {
                    $table->dropColumn('discount_amount');
                }
                if (Schema::connection('landlord')->hasColumn('tenant_payments', 'commission_amount')) {
                    $table->dropColumn('commission_amount');
                }
            });
        }
    }
};
