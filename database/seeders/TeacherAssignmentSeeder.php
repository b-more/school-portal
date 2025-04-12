<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Get teachers grouped by department
        $teachers = Employee::where('role', 'teacher')
                            ->where('status', 'active')
                            ->get()
                            ->groupBy('department');

        // Get classes grouped by department
        $classes = SchoolClass::where('is_active', true)
                            ->get()
                            ->groupBy('department');

        // Get subjects grouped by department
        $subjects = Subject::where('is_active', true)
                            ->get()
                            ->groupBy('department');

        // ECL and Primary teachers are assigned to specific classes
        foreach (['ECL', 'Primary'] as $department) {
            if (!isset($teachers[$department]) || !isset($classes[$department])) {
                continue;
            }

            $deptTeachers = $teachers[$department];
            $deptClasses = $classes[$department];

            // Assign class teachers
            foreach ($deptClasses as $class) {
                // Skip if no teachers available
                if ($deptTeachers->isEmpty()) {
                    continue;
                }

                // Randomly select a teacher for this class
                $teacher = $deptTeachers->random();

                // Create class assignment
                DB::table('class_teacher')->insert([
                    'class_id' => $class->id,
                    'employee_id' => $teacher->id,
                    'role' => 'class_teacher',
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Secondary teachers are assigned to specific subjects
        if (isset($teachers['Secondary']) && isset($subjects['Secondary'])) {
            $secondaryTeachers = $teachers['Secondary'];
            $secondarySubjects = $subjects['Secondary'];
            $secondaryClasses = $classes['Secondary'] ?? collect([]);

            // Group subjects by name (ignoring grade levels)
            $subjectGroups = $secondarySubjects->groupBy('name');

            // Assign teachers to subjects (by subject groups)
            foreach ($subjectGroups as $subjectName => $subjectGroup) {
                // Skip if no teachers available
                if ($secondaryTeachers->isEmpty()) {
                    continue;
                }

                // Select 1-3 teachers who can teach this subject
                $teacherCount = min(rand(1, 3), $secondaryTeachers->count());
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

                        // Find classes that match this subject's grade level
                        $matchingClasses = $secondaryClasses->filter(function ($class) use ($subject) {
                            return $class->grade === $subject->grade_level;
                        });

                        // Assign teacher to teach this subject in relevant classes
                        foreach ($matchingClasses as $class) {
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

        $this->command->info('Successfully seeded teacher assignments!');
        $this->command->info('Class-teacher assignments: ' . DB::table('class_teacher')->count());
        $this->command->info('Teacher-subject assignments: ' . DB::table('employee_subject')->count());
        $this->command->info('Class-subject-teacher assignments: ' . DB::table('class_subject_teacher')->count());
    }
}
