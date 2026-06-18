<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantPaymentsTable extends Migration
{
    public function up()
    {
        if (Schema::getConnection()->getName() === 'tenant') return;

        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('order_id')->unique();
            $table->string('plan_name');
            $table->decimal('gross_amount', 12, 2);
            $table->string('status')->default('pending'); // pending, settlement, cancel, expire
            $table->string('snap_token')->nullable();
            $table->string('payment_type')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        if (Schema::getConnection()->getName() === 'tenant') return;
        Schema::dropIfExists('tenant_payments');
    }
}
