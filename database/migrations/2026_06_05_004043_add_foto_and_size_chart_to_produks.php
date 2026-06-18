<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFotoAndSizeChartToProduks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('harga');
            $table->text('size_chart')->nullable()->after('deskripsi');
        });

        Schema::table('produk_varians', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('nama_varian');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['foto', 'size_chart']);
        });

        Schema::table('produk_varians', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
}
