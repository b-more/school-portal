<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds to assign subjects to teachers based on class levels.
     */
    public function run(): void
    {
        $this->command->info('Starting teacher subject assignment seeding...');

        // Define subject mappings by class/grade level
        $subjectMappings = [
            // ECL Subjects
            'Baby Class' => ['Numeracy', 'Literacy', 'Life Skills', 'Creative Arts'],
            'Middle Class' => ['Numeracy', 'Literacy', 'Life Skills', 'Creative Arts'],
            'Reception' => ['Numeracy', 'Literacy', 'Life Skills', 'Creative Arts'],

            // Primary Subjects
            'Grade 1' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 2' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 3' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 4' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 5' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 6' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],
            'Grade 7' => ['Mathematics', 'English', 'Science', 'Social Studies', 'Art'],

            // Add more mappings as needed
        ];

        // Get all teachers - FIXED: using role_id instead of role
        $teachers = Employee::where('role_id', 2)->get(); // 2 is the Teacher role_id
        $this->command->info("Found {$teachers->count()} teachers to process");

        foreach ($teachers as $teacher) {
            $this->command->info("Processing teacher: {$teacher->name} (ID: {$teacher->id})");

            // Get the classes assigned to this teacher
            $teacherClasses = $teacher->classes()->get();

            if ($teacherClasses->isEmpty()) {
                $this->command->info("No classes assigned to teacher {$teacher->name}. Skipping.");
                continue;
            }

            $this->command->info("Teacher has " . $teacherClasses->count() . " assigned classes");

            // Collect all subjects for this teacher based on their assigned classes
            $subjectNames = [];

            foreach ($teacherClasses as $class) {
                $grade = $class->grade ?? $class->name;
                $this->command->info("Processing class: {$grade}");

                if (isset($subjectMappings[$grade])) {
                    $subjectNames = array_merge($subjectNames, $subjectMappings[$grade]);
                } else {
                    $this->command->warn("No subject mapping found for grade: {$grade}");
                }
            }

            // Remove duplicates
            $subjectNames = array_unique($subjectNames);
            $this->command->info("Assigning subjects: " . implode(', ', $subjectNames));

            // Get subject IDs from names
            $subjects = Subject::whereIn('name', $subjectNames)->get();

            if ($subjects->isEmpty()) {
                $this->command->warn("No matching subjects found in the database.");
                continue;
            }

            $subjectIds = $subjects->pluck('id')->toArray();
            $this->command->info("Found " . count($subjectIds) . " subject IDs in database");

            // Assign subjects to teacher
            $existingSubjects = DB::table('employee_subject')
                ->where('employee_id', $teacher->id)
                ->pluck('subject_id')
                ->toArray();

            $newSubjectIds = array_diff($subjectIds, $existingSubjects);

            if (empty($newSubjectIds)) {
                $this->command->info("No new subjects to assign for teacher {$teacher->name}");
                continue;
            }

            $this->command->info("Assigning " . count($newSubjectIds) . " new subjects to teacher");

            // Create the records to insert
            $records = [];
            $now = now();

            foreach ($newSubjectIds as $subjectId) {
                $records[] = [
                    'employee_id' => $teacher->id,
                    'subject_id' => $subjectId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Insert subject assignments
            DB::table('employee_subject')->insert($records);

            // Also create class-subject-teacher associations for Secondary teachers
            if ($teacher->department === 'Secondary') {
                $this->assignClassSubjectTeacher($teacher, $teacherClasses, $subjects);
            }

            $this->command->info("Completed subject assignments for teacher {$teacher->name}");
        }

        $this->command->info('Teacher subject assignment seeding completed successfully!');
    }

    /**
     * Assign class-subject-teacher relationships for Secondary teachers
     */
    private function assignClassSubjectTeacher($teacher, $classes, $subjects)
    {
        $this->command->info("Creating class-subject-teacher associations for Secondary teacher");

        $existingAssignments = DB::table('class_subject_teacher')
            ->where('employee_id', $teacher->id)
            ->get();

        $existingAssignmentKeys = [];
        foreach ($existingAssignments as $assignment) {
            $existingAssignmentKeys[] = $assignment->class_id . '-' . $assignment->subject_id;
        }

        $records = [];
        $now = now();

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                $key = $class->id . '-' . $subject->id;

                if (in_array($key, $existingAssignmentKeys)) {
                    continue; // Skip existing assignments
                }

                $records[] = [
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'employee_id' => $teacher->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($records)) {
            DB::table('class_subject_teacher')->insert($records);
            $this->command->info("Added " . count($records) . " class-subject-teacher records");
        } else {
            $this->command->info("No new class-subject-teacher records to add");
        }
    }
}
