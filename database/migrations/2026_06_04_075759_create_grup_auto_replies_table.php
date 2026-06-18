<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupAutoRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('grup_auto_replies', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id')->index();
            $table->string('keyword');
            $table->text('balasan');
            $table->boolean('is_exact_match')->default(true);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grup_auto_replies');
    }
}
