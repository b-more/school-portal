<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'description' => 'Full access to all system features',
                'is_active' => true,
            ],
            [
                'name' => 'Teacher',
                'description' => 'Access to teaching and student management',
                'is_active' => true,
            ],
            [
                'name' => 'Student',
                'description' => 'Access to student portal',
                'is_active' => true,
            ],
            [
                'name' => 'Parent',
                'description' => 'Access to parent portal',
                'is_active' => true,
            ],
            [
                'name' => 'Accountant',
                'description' => 'Financial management',
                'is_active' => true,
            ],
            [
                'name' => 'Nurse',
                'description' => 'Health management',
                'is_active' => true,
            ],
            [
                'name' => 'Librarian',
                'description' => 'Library management',
                'is_active' => true,
            ],
            [
                'name' => 'Security',
                'description' => 'School security',
                'is_active' => true,
            ],
            [
                'name' => 'Support',
                'description' => 'General support staff',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
