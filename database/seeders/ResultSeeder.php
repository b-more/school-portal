<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active students
        $students = Student::where('enrollment_status', 'active')->get();
        $subjects = Subject::all();
        $teachers = Employee::where('role', 'teacher')->get();

        // Define exam types, terms and grade ranges
        $examTypes = ['mid-term', 'final', 'quiz', 'assignment'];
        $terms = ['first', 'second', 'third'];
        $year = date('Y');

        // Loop through each student
        foreach ($students as $student) {
            // Get subjects for this student's grade
            $gradeSubjects = $subjects->where('grade_level', $student->grade)->values();

            // If no subjects found for this grade, use random subjects
            if ($gradeSubjects->isEmpty()) {
                $gradeSubjects = $subjects->random(min(5, $subjects->count()));
            }

            // Create 3-5 results per student
            $resultCount = rand(3, 5);

            for ($i = 0; $i < $resultCount; $i++) {
                $subject = $gradeSubjects->random();
                $examType = $examTypes[array_rand($examTypes)];
                $term = $terms[array_rand($terms)];
                $teacher = $teachers->random();

                // Generate realistic marks based on exam type
                if ($examType == 'quiz' || $examType == 'assignment') {
                    $marks = fake()->numberBetween(60, 100);
                } else {
                    $marks = fake()->numberBetween(40, 98);
                }

                // Determine grade based on marks
                $grade = getGradeFromMarks($marks);

                Result::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'exam_type' => $examType,
                    'marks' => $marks,
                    'grade' => $grade,
                    'term' => $term,
                    'year' => $year,
                    'comment' => getResultComment($marks, $grade),
                    'recorded_by' => $teacher->id,
                    'notify_parent' => true,
                ]);
            }
        }
    }
}

/**
 * Determine letter grade from numerical marks
 */
function getGradeFromMarks($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    return 'F';
}

/**
 * Generate realistic teacher comments based on performance
 */
function getResultComment($marks, $grade) {
    // Excellent comments
    if ($marks >= 80) {
        $comments = [
            'Excellent work! Demonstrated outstanding understanding of the material.',
            'Outstanding achievement. Shows remarkable grasp of concepts and excellent application skills.',
            'Exceptional performance. Consistently goes above and beyond expectations.',
            'Excellent results. Shows strong critical thinking and problem-solving abilities.'
        ];
    }
    // Good comments
    elseif ($marks >= 70) {
        $comments = [
            'Good work! Shows solid understanding of key concepts.',
            'Strong performance. Demonstrates good comprehension and application skills.',
            'Good grasp of the material. Some minor areas for improvement.',
            'Performs well consistently. Shows good potential for further growth.'
        ];
    }
    // Average comments
    elseif ($marks >= 60) {
        $comments = [
            'Satisfactory performance. Basic understanding of concepts demonstrated.',
            'Average work. Shows understanding of fundamental concepts but needs to develop deeper insights.',
            'Meets basic expectations. Should focus on strengthening analytical skills.',
            'Adequate performance. Would benefit from more attention to detail.'
        ];
    }
    // Below average comments
    elseif ($marks >= 50) {
        $comments = [
            'Passing but below average. Needs to improve understanding of core concepts.',
            'Barely meets minimum requirements. Significant improvement needed.',
            'Struggles with key concepts. Would benefit from extra help and practice.',
            'Needs considerable improvement. Should seek additional support.'
        ];
    }
    // Failing comments
    else {
        $comments = [
            'Does not meet minimum requirements. Urgent intervention needed.',
            'Fails to demonstrate basic understanding of the subject matter.',
            'Requires immediate attention and support to address significant gaps in knowledge.',
            'Not meeting course expectations. Recommend arranging a parent-teacher conference.'
        ];
    }

    return $comments[array_rand($comments)];
}
