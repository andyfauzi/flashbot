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
        if (Schema::connection('landlord')->hasTable('package_menus')) {
            Schema::connection('landlord')->table('package_menus', function (Blueprint $table) {
                if (!Schema::connection('landlord')->hasColumn('package_menus', 'show_on_landing_page')) {
                    $table->boolean('show_on_landing_page')->default(true)->after('business_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::connection('landlord')->hasTable('package_menus')) {
            Schema::connection('landlord')->table('package_menus', function (Blueprint $table) {
                if (Schema::connection('landlord')->hasColumn('package_menus', 'show_on_landing_page')) {
                    $table->dropColumn('show_on_landing_page');
                }
            });
        }
    }
};
