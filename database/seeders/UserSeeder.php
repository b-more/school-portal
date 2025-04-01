<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear any existing users to prevent duplicates
        // Comment this out if you don't want to delete existing users
        // DB::table('users')->truncate();

        // Check if admin user exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'phone' => '260977123456',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'last_login_at' => now(),
            ]);
        }

        // Check if teacher user exists
        if (!User::where('email', 'teacher@example.com')->exists()) {
            User::create([
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'phone' => '260977123457',
                'username' => 'teacher',
                'password' => Hash::make('password'),
                'status' => 'active',
                'last_login_at' => now(),
            ]);
        }

        // Check if parent user exists
        if (!User::where('email', 'parent@example.com')->exists()) {
            User::create([
                'name' => 'Parent User',
                'email' => 'parent@example.com',
                'phone' => '260977123458',
                'username' => 'parent',
                'password' => Hash::make('password'),
                'status' => 'active',
                'last_login_at' => now(),
            ]);
        }

        // Create additional users - only if fewer than 10 additional users exist
        $additionalUserCount = User::count() - 3; // Subtract the 3 specific users
        if ($additionalUserCount < 10) {
            $usersToCreate = 10 - $additionalUserCount;
            User::factory($usersToCreate)->create();
        }
    }
}
