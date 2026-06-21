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
        Schema::table('identitas_tokos', function (Blueprint $table) {
            if (!Schema::hasColumn('identitas_tokos', 'jam_buka')) {
                $table->time('jam_buka')->nullable()->after('jenis_layanan');
            }
            if (!Schema::hasColumn('identitas_tokos', 'jam_tutup')) {
                $table->time('jam_tutup')->nullable()->after('jam_buka');
            }
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
            $table->dropColumn(['jam_buka', 'jam_tutup']);
        });
    }
};
