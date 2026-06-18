<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('grup_settings', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id')->index();
            $table->string('kunci'); // The command alias key, e.g. cmd_bantuan
            $table->string('nilai'); // The configured keyword, e.g. !bantuan
            $table->timestamps();
            
            $table->unique(['grup_id', 'kunci']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grup_settings');
    }
}
