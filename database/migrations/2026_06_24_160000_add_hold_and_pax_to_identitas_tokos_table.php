<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoldAndPaxToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->integer('hold_duration_hours')->default(2)->after('minimal_jam_reservasi');
            $table->integer('max_pax_per_reservation')->default(20)->after('hold_duration_hours');
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
            $table->dropColumn(['hold_duration_hours', 'max_pax_per_reservation']);
        });
    }
}
