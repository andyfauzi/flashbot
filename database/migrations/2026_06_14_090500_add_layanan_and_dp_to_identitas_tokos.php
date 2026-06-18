<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLayananAndDpToIdentitasTokos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identitas_tokos', function (Blueprint $table) {
            if (!Schema::hasColumn('identitas_tokos', 'jenis_layanan')) {
                $table->enum('jenis_layanan', ['dine_in', 'take_away', 'keduanya'])->default('keduanya')->after('karakter_bot');
            }
            if (!Schema::hasColumn('identitas_tokos', 'wajib_dp_reservasi')) {
                $table->boolean('wajib_dp_reservasi')->default(false)->after('jenis_layanan');
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
            if (Schema::hasColumn('identitas_tokos', 'jenis_layanan')) {
                $table->dropColumn('jenis_layanan');
            }
            if (Schema::hasColumn('identitas_tokos', 'wajib_dp_reservasi')) {
                $table->dropColumn('wajib_dp_reservasi');
            }
        });
    }
}
