<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenant_requests', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('subdomain')->unique();
            $table->string('owner_name');
            $table->string('email');
            $table->string('whatsapp_number');
            $table->text('store_address');
            $table->string('jenis_layanan');
            $table->string('skala_bisnis')->nullable();
            $table->string('plan')->default('starter');
            $table->boolean('is_trial')->default(false);
            $table->string('google_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tenant_requests');
    }
}
