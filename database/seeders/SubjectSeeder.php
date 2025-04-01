<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            // Primary Grades (1-7)
            ['name' => 'Mathematics', 'code' => 'MATH-P', 'grade_level' => 'Grade 1', 'description' => 'Basic mathematics for Grade 1'],
            ['name' => 'English', 'code' => 'ENG-P', 'grade_level' => 'Grade 1', 'description' => 'English language skills for Grade 1'],
            ['name' => 'Science', 'code' => 'SCI-P', 'grade_level' => 'Grade 1', 'description' => 'Introduction to science for Grade 1'],
            ['name' => 'Social Studies', 'code' => 'SOC-P', 'grade_level' => 'Grade 1', 'description' => 'Social studies for Grade 1'],

            ['name' => 'Mathematics', 'code' => 'MATH-P2', 'grade_level' => 'Grade 2', 'description' => 'Basic mathematics for Grade 2'],
            ['name' => 'English', 'code' => 'ENG-P2', 'grade_level' => 'Grade 2', 'description' => 'English language skills for Grade 2'],
            ['name' => 'Science', 'code' => 'SCI-P2', 'grade_level' => 'Grade 2', 'description' => 'Introduction to science for Grade 2'],
            ['name' => 'Social Studies', 'code' => 'SOC-P2', 'grade_level' => 'Grade 2', 'description' => 'Social studies for Grade 2'],

            ['name' => 'Mathematics', 'code' => 'MATH-P3', 'grade_level' => 'Grade 3', 'description' => 'Basic mathematics for Grade 3'],
            ['name' => 'English', 'code' => 'ENG-P3', 'grade_level' => 'Grade 3', 'description' => 'English language skills for Grade 3'],
            ['name' => 'Science', 'code' => 'SCI-P3', 'grade_level' => 'Grade 3', 'description' => 'Introduction to science for Grade 3'],
            ['name' => 'Social Studies', 'code' => 'SOC-P3', 'grade_level' => 'Grade 3', 'description' => 'Social studies for Grade 3'],

            // Junior Secondary (Grades 8-9)
            ['name' => 'Mathematics', 'code' => 'MATH-J', 'grade_level' => 'Grade 8', 'description' => 'Mathematics for Grade 8'],
            ['name' => 'English Language', 'code' => 'ENG-J', 'grade_level' => 'Grade 8', 'description' => 'English language for Grade 8'],
            ['name' => 'Integrated Science', 'code' => 'SCI-J', 'grade_level' => 'Grade 8', 'description' => 'Integrated Science for Grade 8'],
            ['name' => 'Social Studies', 'code' => 'SOC-J', 'grade_level' => 'Grade 8', 'description' => 'Social Studies for Grade 8'],
            ['name' => 'Geography', 'code' => 'GEO-J', 'grade_level' => 'Grade 8', 'description' => 'Geography for Grade 8'],
            ['name' => 'History', 'code' => 'HIS-J', 'grade_level' => 'Grade 8', 'description' => 'History for Grade 8'],
            ['name' => 'Religious Education', 'code' => 'RE-J', 'grade_level' => 'Grade 8', 'description' => 'Religious Education for Grade 8'],

            ['name' => 'Mathematics', 'code' => 'MATH-J9', 'grade_level' => 'Grade 9', 'description' => 'Mathematics for Grade 9'],
            ['name' => 'English Language', 'code' => 'ENG-J9', 'grade_level' => 'Grade 9', 'description' => 'English language for Grade 9'],
            ['name' => 'Integrated Science', 'code' => 'SCI-J9', 'grade_level' => 'Grade 9', 'description' => 'Integrated Science for Grade 9'],
            ['name' => 'Social Studies', 'code' => 'SOC-J9', 'grade_level' => 'Grade 9', 'description' => 'Social Studies for Grade 9'],
            ['name' => 'Geography', 'code' => 'GEO-J9', 'grade_level' => 'Grade 9', 'description' => 'Geography for Grade 9'],
            ['name' => 'History', 'code' => 'HIS-J9', 'grade_level' => 'Grade 9', 'description' => 'History for Grade 9'],
            ['name' => 'Religious Education', 'code' => 'RE-J9', 'grade_level' => 'Grade 9', 'description' => 'Religious Education for Grade 9'],
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'grade_level' => $subject['grade_level'],
                'description' => $subject['description'],
                'is_active' => true,
            ]);
        }

        // Assign teachers to subjects
        $teachers = Employee::where('role', 'teacher')->get();
        $subjects = Subject::all();

        // Check if we have any teachers before trying to assign them
        if ($teachers->count() > 0) {
            foreach ($subjects as $subject) {
                // Assign 1-2 teachers to each subject (but not more than available)
                $teacherCount = min(rand(1, 2), $teachers->count());
                $selectedTeachers = $teachers->random($teacherCount);

                foreach ($selectedTeachers as $teacher) {
                    $subject->employees()->attach($teacher->id);
                }
            }
        } else {
            // No teachers found, display a message
            $this->command->info('No teachers found in the database. Subjects created without teacher assignments.');
        }
    }
}
