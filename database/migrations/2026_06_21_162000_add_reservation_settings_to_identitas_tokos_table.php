<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationSettingsToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            $table->decimal('nominal_dp_reservasi', 15, 2)->default(0)->after('wajib_dp_reservasi');
            $table->time('jam_buka')->nullable()->after('nominal_dp_reservasi');
            $table->time('jam_tutup')->nullable()->after('jam_buka');
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
            $table->dropColumn(['nominal_dp_reservasi', 'jam_buka', 'jam_tutup']);
        });
    }
}
