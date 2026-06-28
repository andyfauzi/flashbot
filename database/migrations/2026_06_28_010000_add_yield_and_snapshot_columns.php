<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYieldAndSnapshotColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produk_varians', function (Blueprint $table) {
            if (!Schema::hasColumn('produk_varians', 'resep_yield')) {
                $table->integer('resep_yield')->default(1)->after('overhead_cost');
            }
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan_items', 'hpp_snapshot')) {
                $table->decimal('hpp_snapshot', 15, 2)->default(0)->after('subtotal');
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
        Schema::table('produk_varians', function (Blueprint $table) {
            if (Schema::hasColumn('produk_varians', 'resep_yield')) {
                $table->dropColumn('resep_yield');
            }
        });

        Schema::table('pesanan_items', function (Blueprint $table) {
            if (Schema::hasColumn('pesanan_items', 'hpp_snapshot')) {
                $table->dropColumn('hpp_snapshot');
            }
        });
    }
}
