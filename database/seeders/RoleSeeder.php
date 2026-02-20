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
            // ID 1
            [
                'name' => 'Admin',
                'description' => 'Full access to all system features',
                'is_active' => true,
            ],
            // ID 2
            [
                'name' => 'Teacher',
                'description' => 'Access to teaching and student management',
                'is_active' => true,
            ],
            // ID 3
            [
                'name' => 'Student',
                'description' => 'Access to student portal',
                'is_active' => true,
            ],
            // ID 4
            [
                'name' => 'Parent',
                'description' => 'Access to parent portal',
                'is_active' => true,
            ],
            // ID 5
            [
                'name' => 'Accountant',
                'description' => 'Financial management',
                'is_active' => true,
            ],
            // ID 6
            [
                'name' => 'Nurse',
                'description' => 'Health management',
                'is_active' => true,
            ],
            // ID 7
            [
                'name' => 'Librarian',
                'description' => 'Library management',
                'is_active' => true,
            ],
            // ID 8
            [
                'name' => 'Security',
                'description' => 'School security',
                'is_active' => true,
            ],
            // ID 9
            [
                'name' => 'Support',
                'description' => 'General support staff',
                'is_active' => true,
            ],
            // ID 10
            [
                'name' => 'Clinician',
                'description' => 'Medical clinic staff',
                'is_active' => true,
            ],
            // ID 11
            [
                'name' => 'Director',
                'description' => 'School director with oversight responsibilities',
                'is_active' => true,
            ],
            // ID 12
            [
                'name' => 'Dean of Primary',
                'description' => 'Dean responsible for primary section',
                'is_active' => true,
            ],
            // ID 13
            [
                'name' => 'Dean of Secondary',
                'description' => 'Dean responsible for secondary section',
                'is_active' => true,
            ],
            // ID 14
            [
                'name' => 'Driver',
                'description' => 'School bus driver',
                'is_active' => true,
            ],
            // ID 15
            [
                'name' => 'Head Teacher Primary',
                'description' => 'Head of primary section with full access to primary school management',
                'is_active' => true,
            ],
            // ID 16
            [
                'name' => 'Head Teacher Secondary',
                'description' => 'Head of secondary section with full access to secondary school management',
                'is_active' => true,
            ],
            // ID 17
            [
                'name' => 'Deputy Head Primary',
                'description' => 'Deputy head of primary section',
                'is_active' => true,
            ],
            // ID 18
            [
                'name' => 'Deputy Head Secondary',
                'description' => 'Deputy head of secondary section',
                'is_active' => true,
            ],
            // ID 19
            [
                'name' => 'School Secretary',
                'description' => 'School secretary with access to student records, attendance, communication, and events',
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
