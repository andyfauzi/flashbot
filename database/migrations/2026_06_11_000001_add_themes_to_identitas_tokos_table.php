<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThemesToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->string('tema_portal')->default('cool')->after('qris_path');
            $table->string('tema_desktop')->default('cool')->after('tema_portal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->dropColumn(['tema_portal', 'tema_desktop']);
        });
    }
}
