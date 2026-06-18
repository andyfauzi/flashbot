<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'akses_pos',
            'akses_hpp',
            'akses_kas',
            'akses_karyawan',
            'akses_wa',
            'akses_laporan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Migrate existing users to have basic permissions based on their role
        $users = User::all();
        foreach ($users as $user) {
            if ($user->role === 'admin') {
                $user->syncPermissions($permissions);
            } elseif ($user->role === 'kasir') {
                $user->syncPermissions(['akses_pos', 'akses_kas']);
            }
            // owner is handled by Gate::before
        }
    }
}
