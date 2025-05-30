<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class ClassTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing assignments
        DB::table('class_teacher')->truncate();

        // Define class-teacher assignments
        $assignments = [
            // Primary Classes
            [
                'class_name' => 'Baby Class A',
                'teacher_name' => 'Mary Johnson',
                'role' => 'class_teacher',
                'is_primary' => true
            ],
            [
                'class_name' => 'Baby Class A',
                'teacher_name' => 'Sarah Wilson',
                'role' => 'assistant_teacher',
                'is_primary' => false
            ],
            [
                'class_name' => 'Grade 1A',
                'teacher_name' => 'Jennifer Smith',
                'role' => 'class_teacher',
                'is_primary' => true
            ],
            [
                'class_name' => 'Grade 2A',
                'teacher_name' => 'Patricia Brown',
                'role' => 'class_teacher',
                'is_primary' => true
            ],

            // Secondary Classes
            [
                'class_name' => 'Grade 8A',
                'teacher_name' => 'Dr. Michael Anderson',
                'role' => 'class_teacher',
                'is_primary' => true
            ],
            [
                'class_name' => 'Grade 8A',
                'teacher_name' => 'Prof. Lisa Martinez',
                'role' => 'subject_teacher',
                'is_primary' => false
            ],
            [
                'class_name' => 'Grade 9A',
                'teacher_name' => 'Mr. Robert Taylor',
                'role' => 'class_teacher',
                'is_primary' => true
            ],
        ];

        foreach ($assignments as $assignment) {
            $class = SchoolClass::where('name', $assignment['class_name'])->first();
            $teacher = Teacher::where('name', $assignment['teacher_name'])->first();

            if ($class && $teacher) {
                $class->teachers()->attach($teacher->id, [
                    'role' => $assignment['role'],
                    'is_primary' => $assignment['is_primary'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->info("Assigned {$teacher->name} to {$class->name} as {$assignment['role']}");
            } else {
                $this->command->warn("Could not find class '{$assignment['class_name']}' or teacher '{$assignment['teacher_name']}'");
            }
        }

        // Auto-assign remaining primary teachers to primary classes
        $this->autoAssignPrimaryTeachers();

        // Auto-assign secondary teachers to secondary classes
        $this->autoAssignSecondaryTeachers();
    }

    private function autoAssignPrimaryTeachers()
    {
        $primaryClasses = SchoolClass::where('department', 'Primary')
            ->whereDoesntHave('teachers', function($query) {
                $query->where('role', 'class_teacher');
            })
            ->where('is_active', true)
            ->get();

        $availablePrimaryTeachers = Teacher::whereNull('specialization')
            ->whereDoesntHave('schoolClasses', function($query) {
                $query->where('role', 'class_teacher');
            })
            ->where('is_active', true)
            ->get();

        foreach ($primaryClasses as $index => $class) {
            if (isset($availablePrimaryTeachers[$index])) {
                $teacher = $availablePrimaryTeachers[$index];

                $class->teachers()->attach($teacher->id, [
                    'role' => 'class_teacher',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->info("Auto-assigned {$teacher->name} to {$class->name}");
            }
        }
    }

    private function autoAssignSecondaryTeachers()
    {
        $secondaryClasses = SchoolClass::where('department', 'Secondary')
            ->whereDoesntHave('teachers', function($query) {
                $query->where('role', 'class_teacher');
            })
            ->where('is_active', true)
            ->get();

        $availableSecondaryTeachers = Teacher::whereNotNull('specialization')
            ->whereDoesntHave('schoolClasses', function($query) {
                $query->where('role', 'class_teacher');
            })
            ->where('is_active', true)
            ->get();

        foreach ($secondaryClasses as $index => $class) {
            if (isset($availableSecondaryTeachers[$index])) {
                $teacher = $availableSecondaryTeachers[$index];

                $class->teachers()->attach($teacher->id, [
                    'role' => 'class_teacher',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $this->command->info("Auto-assigned {$teacher->name} to {$class->name}");
            }
        }
    }
}
