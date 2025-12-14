<?php

namespace Database\Seeders;

use App\Models\StaffDesignation;
use Illuminate\Database\Seeder;

class StaffDesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Dean of Teachers',
                'code' => 'dean_teachers',
                'description' => 'Oversees all teachers within the section. Reports to Head Teacher.',
                'section' => 'both',
                'hierarchy_level' => 1,
                'permissions' => [
                    'view_section_teachers',
                    'view_section_students',
                    'view_section_reports',
                    'manage_homework',
                    'manage_results',
                    'mentor_teachers',
                    'approve_leave',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Senior Teacher',
                'code' => 'senior_teacher',
                'description' => 'Experienced teacher with mentoring responsibilities.',
                'section' => 'both',
                'hierarchy_level' => 2,
                'permissions' => [
                    'view_section_students',
                    'view_section_reports',
                    'manage_homework',
                    'manage_results',
                    'mentor_teachers',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Subject Coordinator',
                'code' => 'subject_coordinator',
                'description' => 'Coordinates curriculum and teaching for a specific subject area.',
                'section' => 'both',
                'hierarchy_level' => 2,
                'permissions' => [
                    'view_section_students',
                    'manage_curriculum',
                    'manage_homework',
                    'manage_results',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Class Teacher',
                'code' => 'class_teacher',
                'description' => 'Primary teacher responsible for a specific class section.',
                'section' => 'both',
                'hierarchy_level' => 3,
                'permissions' => [
                    'manage_homework',
                    'manage_results',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Teacher',
                'code' => 'teacher',
                'description' => 'Standard teaching staff member.',
                'section' => 'both',
                'hierarchy_level' => 3,
                'permissions' => [
                    'manage_homework',
                    'manage_results',
                ],
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($designations as $designation) {
            StaffDesignation::updateOrCreate(
                ['code' => $designation['code']],
                $designation
            );
        }

        $this->command->info('Staff designations seeded successfully!');
    }
}
