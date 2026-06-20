<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This table is stored in the landlord database
        Schema::connection('landlord')->create('package_menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key')->unique();
            $table->string('menu_label');
            $table->string('category')->nullable();
            $table->boolean('gratis_enabled')->default(false);
            $table->boolean('starter_enabled')->default(false);
            $table->boolean('pro_enabled')->default(false);
            $table->boolean('business_enabled')->default(false);
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
        Schema::connection('landlord')->dropIfExists('package_menus');
    }
};
