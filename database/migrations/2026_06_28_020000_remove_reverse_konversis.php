<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveReverseKonversis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('satuan_konversis')
            ->where('satuan_awal', 'Gram')
            ->where('satuan_akhir', 'Kilogram')
            ->delete();

        DB::table('satuan_konversis')
            ->where('satuan_awal', 'ml')
            ->where('satuan_akhir', 'Liter')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse
    }
}
