<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBotIdentityToIdentitasTokosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            if (!Schema::hasColumn('identitas_tokos', 'nama_bot')) {
                $table->string('nama_bot')->default('Teta Assistant')->after('is_broadcast_approved');
            }
            if (!Schema::hasColumn('identitas_tokos', 'karakter_bot')) {
                $table->string('karakter_bot')->default('Customer Service Virtual (AI) ramah')->after('nama_bot');
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
            if (Schema::hasColumn('identitas_tokos', 'nama_bot')) {
                $table->dropColumn('nama_bot');
            }
            if (Schema::hasColumn('identitas_tokos', 'karakter_bot')) {
                $table->dropColumn('karakter_bot');
            }
        });
    }
}
