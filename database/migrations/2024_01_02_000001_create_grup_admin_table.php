<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrupAdminTable extends Migration
{
    public function up()
    {
        Schema::create('grup_admin', function (Blueprint $table) {
            $table->id();
            $table->string('grup_id', 100)->comment('ID grup WA');
            $table->string('nomor_admin', 100)->comment('Nomor WA admin');
            $table->string('nama_admin', 100)->nullable()->comment('Nama admin');
            $table->string('ditambahkan_oleh', 100)->nullable()->comment('Nomor yang menambahkan');
            $table->timestamps();

            $table->unique(['grup_id', 'nomor_admin']);
            $table->index('grup_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grup_admin');
    }
}