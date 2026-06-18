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
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            $table->text('meta_access_token_encrypted')->nullable()->after('plan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            $table->dropColumn('meta_access_token_encrypted');
        });
    }
};
