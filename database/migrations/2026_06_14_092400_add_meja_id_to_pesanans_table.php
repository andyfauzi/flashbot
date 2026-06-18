<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->unsignedBigInteger('meja_id')->nullable()->after('status');
            // Assuming tenant isolation logic applies, we don't necessarily need a strict FK if it's cross-tenant, but it's safe to just leave it as an index or FK.
            // $table->foreign('meja_id')->references('id')->on('mejas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pesanans', function (Blueprint $table) {
            $table->dropColumn('meja_id');
        });
    }
};
