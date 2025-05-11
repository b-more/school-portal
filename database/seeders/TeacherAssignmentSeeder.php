<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Constants\RoleConstants; // Optional: if you're using role constants

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing assignments
        DB::table('class_teacher')->truncate();
        DB::table('employee_subject')->truncate();
        DB::table('class_subject_teacher')->truncate();

        // Specific teacher assignments based on provided list
        $this->assignSpecificTeachers();

        // Get remaining teachers and classes for random assignments
        $assignedTeacherIds = DB::table('class_teacher')->pluck('employee_id')->unique()->toArray();

        // FIX: Change 'role' to 'role_id'
        $teachers = Employee::where('role_id', 2) // 2 is Teacher role_id
                            ->where('status', 'active')
                            ->whereNotIn('id', $assignedTeacherIds)
                            ->get()
                            ->groupBy('department');

        $assignedClassIds = DB::table('class_teacher')->pluck('class_id')->unique()->toArray();

        $classes = SchoolClass::where('status', 'active')
                            ->whereNotIn('id', $assignedClassIds)
                            ->get()
                            ->groupBy('department');

        // Get subjects grouped by department
        $subjects = Subject::where('is_active', true)
                            ->get()
                            ->groupBy('department');

        // Assignment for Secondary teachers to subjects
        $this->assignSecondaryTeachersToSubjects($teachers, $subjects, $classes);

        $this->command->info('Successfully seeded teacher assignments!');
        $this->command->info('Class-teacher assignments: ' . DB::table('class_teacher')->count());
        $this->command->info('Teacher-subject assignments: ' . DB::table('employee_subject')->count());
        $this->command->info('Class-subject-teacher assignments: ' . DB::table('class_subject_teacher')->count());
    }

    /**
     * Assign specific teachers to classes based on the provided list
     */
    private function assignSpecificTeachers(): void
    {
        $teacherAssignments = [
            // ECL Department
            ['name' => 'Chungu', 'class' => 'Baby Class'],
            ['name' => 'Zunda', 'class' => 'Middle Class'],
            ['name' => 'Constance', 'class' => 'Reception'],

            // Primary Department
            ['name' => 'Musa Doris', 'class' => 'Grade 1'],
            ['name' => 'Musakanya Mutale', 'class' => 'Grade 2'],
            ['name' => 'Eunice Kansa', 'class' => 'Grade 3'],
            ['name' => 'Euelle Sinyangwe', 'class' => 'Grade 4'],
            ['name' => 'Mukupa Agness', 'class' => 'Grade 5'],
            ['name' => 'Mubisa Martin', 'class' => 'Grade 6'],
            ['name' => 'Kopakopa Leonard', 'class' => 'Grade 7'],

            // Secondary Department
            ['name' => 'Kaposhi', 'class' => 'Form 1 Archivers'],
            ['name' => 'Muonda Bwalya', 'class' => 'Form 1 Success'],
            ['name' => 'Chibwe Quintino', 'class' => 'Form 2 Lilly'],
            ['name' => 'Mwaba Breven', 'class' => 'Form 2 Lotus'],
            ['name' => 'Sintomba Freddy', 'class' => 'Form 3 A'],
            ['name' => 'Mulenga Vincent', 'class' => 'Form 3 B'],
            ['name' => 'Bwalya Sylvester', 'class' => 'Form 4'],
            ['name' => 'Singongo Bruce', 'class' => 'Form 5'],
        ];

        foreach ($teacherAssignments as $assignment) {
            // Find the teacher by name
            $user = User::where('name', $assignment['name'])->first();

            if (!$user) {
                $this->command->error("User {$assignment['name']} not found. Skipping class assignment.");
                continue;
            }

            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                $this->command->error("Employee record for {$assignment['name']} not found. Skipping class assignment.");
                continue;
            }

            // Find the class
            $class = SchoolClass::where('name', $assignment['class'])->first();

            if (!$class) {
                $this->command->error("Class {$assignment['class']} not found. Skipping assignment for {$assignment['name']}.");
                continue;
            }

            // Create class-teacher assignment
            DB::table('class_teacher')->insert([
                'class_id' => $class->id,
                'employee_id' => $employee->id,
                'role' => 'Class Teacher',
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("Assigned {$assignment['name']} to {$assignment['class']}");
        }

        // Special assignments for leadership positions
        $this->assignLeadershipRoles();
    }

    /**
     * Assign leadership roles
     */
    private function assignLeadershipRoles(): void
    {
        $leadershipRoles = [
            ['name' => 'Mercy Kapelenga', 'position' => 'Dean of Senior Teachers', 'department' => 'Primary'],
            ['name' => 'Sylvester Lupando', 'position' => 'Headteacher', 'department' => 'Administration'],
            ['name' => 'Tiza Nkhomo', 'position' => 'Deputy Headteacher', 'department' => 'Administration'],
            ['name' => 'Singongo Bruce', 'position' => 'Head of Department', 'department' => 'Secondary'],
        ];

        foreach ($leadershipRoles as $role) {
            // Find the user
            $user = User::where('name', $role['name'])->first();

            if (!$user) {
                $this->command->error("User {$role['name']} not found. Skipping leadership role assignment.");
                continue;
            }

            // Update employee record with the position
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                $this->command->error("Employee record for {$role['name']} not found. Skipping leadership role assignment.");
                continue;
            }

            $employee->position = $role['position'];
            $employee->department = $role['department'];
            $employee->save();

            $this->command->info("Assigned {$role['name']} as {$role['position']} in {$role['department']} department");
        }
    }

    /**
     * Assign secondary teachers to subjects and classes
     */
    private function assignSecondaryTeachersToSubjects($teachers, $subjects, $classes): void
    {
        // Only proceed if we have secondary teachers and subjects
        if (!isset($teachers['Secondary']) || !isset($subjects['Secondary'])) {
            return;
        }

        $secondaryTeachers = $teachers['Secondary'];
        $secondarySubjects = $subjects['Secondary'];
        $secondaryClasses = $classes['Secondary'] ?? collect([]);

        // Define core subjects
        $coreSubjects = ['Mathematics', 'English Language', 'Science', 'Social Studies', 'Religious Education'];

        // Find secondary teachers who are already assigned to classes
        $formTeachers = Employee::whereHas('classes', function($query) {
            $query->whereHas('department', function($q) {
                $q->where('department', 'Secondary');
            });
        })->get();

        // Group subjects by name (ignoring grade levels)
        $subjectGroups = $secondarySubjects->groupBy('name');

        // First, assign form teachers to core subjects in their classes
        foreach ($formTeachers as $teacher) {
            // Get teacher's assigned classes
            $teacherClasses = $teacher->classes;

            if ($teacherClasses->isEmpty()) {
                continue;
            }

            // Assign 1-2 core subjects to this form teacher for their class
            $subjectsToAssign = min(rand(1, 2), count($coreSubjects));
            $selectedSubjects = array_rand(array_flip($coreSubjects), $subjectsToAssign);

            if (!is_array($selectedSubjects)) {
                $selectedSubjects = [$selectedSubjects];
            }

            foreach ($selectedSubjects as $subjectName) {
                if (!isset($subjectGroups[$subjectName])) {
                    continue;
                }

                foreach ($subjectGroups[$subjectName] as $subject) {
                    // Create subject-teacher assignment
                    DB::table('employee_subject')->insertOrIgnore([
                        'employee_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign teacher to teach this subject in their classes
                    foreach ($teacherClasses as $class) {
                        DB::table('class_subject_teacher')->insertOrIgnore([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'employee_id' => $teacher->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Then assign remaining subjects to other secondary teachers
        foreach ($subjectGroups as $subjectName => $subjectGroup) {
            // Skip if no teachers available
            if ($secondaryTeachers->isEmpty()) {
                continue;
            }

            // Check if this subject already has enough teachers assigned
            $assignedTeacherCount = DB::table('employee_subject')
                ->whereIn('subject_id', $subjectGroup->pluck('id'))
                ->distinct('employee_id')
                ->count('employee_id');

            if ($assignedTeacherCount >= 2) {
                continue; // Skip if at least 2 teachers are already assigned
            }

            // Select 1-2 teachers who can teach this subject
            $teacherCount = min(rand(1, 2), $secondaryTeachers->count());
            $selectedTeachers = $secondaryTeachers->random($teacherCount);

            foreach ($selectedTeachers as $teacher) {
                // Assign teacher to all grade levels of this subject
                foreach ($subjectGroup as $subject) {
                    // Create subject-teacher assignment
                    DB::table('employee_subject')->insert([
                        'employee_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Assign to a random subset of classes
                    $classCount = min(rand(1, 3), $secondaryClasses->count());
                    $selectedClasses = $secondaryClasses->random($classCount);

                    foreach ($selectedClasses as $class) {
                        DB::table('class_subject_teacher')->insert([
                            'class_id' => $class->id,
                            'subject_id' => $subject->id,
                            'employee_id' => $teacher->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
