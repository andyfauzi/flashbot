<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationFieldsToReservasisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservasis', function (Blueprint $table) {
            // Modify status enum. In Laravel 8, modifying enums can be tricky.
            // Using DB::statement is safer for enums in MySQL.
            // We will add the new statuses if they don't exist.
            // Existing: 'menunggu', 'dikonfirmasi', 'selesai', 'batal'
            // New ones to add: 'on_hold', 'ditolak', 'kedaluwarsa'
        });

        // Using raw statement for modifying ENUM in MySQL
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE reservasis MODIFY COLUMN status ENUM('menunggu', 'on_hold', 'dikonfirmasi', 'selesai', 'batal', 'ditolak', 'kedaluwarsa') DEFAULT 'menunggu'");

        Schema::table('reservasis', function (Blueprint $table) {
            $table->timestamp('hold_expires_at')->nullable()->after('status');
            $table->string('rejection_reason')->nullable()->after('hold_expires_at');
            $table->json('pre_order_items')->nullable()->after('rejection_reason');
            // pax is redundant with jumlah_orang, so we will use jumlah_orang
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservasis', function (Blueprint $table) {
            $table->dropColumn(['hold_expires_at', 'rejection_reason', 'pre_order_items']);
        });
        
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE reservasis MODIFY COLUMN status ENUM('menunggu', 'dikonfirmasi', 'selesai', 'batal') DEFAULT 'menunggu'");
    }
}
