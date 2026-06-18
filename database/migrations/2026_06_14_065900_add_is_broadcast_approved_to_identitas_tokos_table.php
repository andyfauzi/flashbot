<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsBroadcastApprovedToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            if (!Schema::hasColumn('identitas_tokos', 'is_broadcast_approved')) {
                $table->boolean('is_broadcast_approved')->default(false)->after('tema_desktop');
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
            if (Schema::hasColumn('identitas_tokos', 'is_broadcast_approved')) {
                $table->dropColumn('is_broadcast_approved');
            }
        });
    }
}
