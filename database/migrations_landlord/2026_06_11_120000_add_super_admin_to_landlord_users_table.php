<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add super admin columns to landlord users table.
 *
 * This migration runs on the LANDLORD connection (db_flashbot), not on
 * any tenant database. It is stored in database/migrations_landlord so it
 * is never executed accidentally during tenant database provisioning.
 *
 * Run with:
 *   php artisan migrate --path=database/migrations_landlord --database=landlord
 */
return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'landlord';

    public function up(): void
    {
        Schema::connection('landlord')->table('users', function (Blueprint $table) {
            // Flag: true only for the platform owner / super admin
            $table->boolean('is_super_admin')
                  ->default(false)
                  ->after('email');

            // Audit trail: last time this account accessed the super-admin panel
            $table->timestamp('last_super_admin_access')
                  ->nullable()
                  ->after('is_super_admin');
        });
    }

    public function down(): void
    {
        Schema::connection('landlord')->table('users', function (Blueprint $table) {
            $table->dropColumn(['is_super_admin', 'last_super_admin_access']);
        });
    }
};
