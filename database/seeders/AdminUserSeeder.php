<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Cek apakah user admin sudah ada
        $user = User::where('email', 'admin@example.com')->first();
        
        if (!$user) {
            User::create([
                'name'     => 'Administrator Chatbot',
                'email'    => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'is_super_admin' => true,
            ]);
        }
    }
}
