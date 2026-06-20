<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::connection('landlord')->hasTable('landlord_help_guides')) {
            Schema::connection('landlord')->create('landlord_help_guides', function (Blueprint $table) {
                $table->id();
                $table->string('pertanyaan');
                $table->text('jawaban');
                $table->integer('urutan')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('landlord_help_guides');
    }
};
