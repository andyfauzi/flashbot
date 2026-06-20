<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('landlord')->table('users', function (Blueprint $table) {
            if (!Schema::connection('landlord')->hasColumn('users', 'is_sales')) {
                $table->boolean('is_sales')->default(false)->after('is_super_admin');
            }
        });
    }

    public function down()
    {
        Schema::connection('landlord')->table('users', function (Blueprint $table) {
            if (Schema::connection('landlord')->hasColumn('users', 'is_sales')) {
                $table->dropColumn('is_sales');
            }
        });
    }
};
