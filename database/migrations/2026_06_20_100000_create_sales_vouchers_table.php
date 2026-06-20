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
        if (!Schema::connection('landlord')->hasTable('sales_vouchers')) {
            Schema::connection('landlord')->create('sales_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('kode_voucher')->unique();
                $table->string('nama_sales');
                $table->string('no_wa_sales')->nullable();
                $table->integer('diskon_persen')->default(0);
                $table->integer('komisi_persen')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('sales_vouchers');
    }
};
