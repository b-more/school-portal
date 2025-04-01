<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class HomeworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get teachers, subjects, and grades
        $teachers = Employee::where('role', 'teacher')->get();
        $subjects = Subject::all();
        $allGrades = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 8', 'Grade 9'];

        // Create 30 homework assignments across different subjects and grades
        for ($i = 1; $i <= 30; $i++) {
            $subject = $subjects->random();
            $teacher = $teachers->random();
            $grade = $subject->grade_level;

            // If subject doesn't have grade, assign a random one
            if (empty($grade)) {
                $grade = $allGrades[array_rand($allGrades)];
            }

            // Create various homework assignments with different statuses
            $status = $i <= 20 ? 'active' : 'completed';

            // Use dates that make sense - completed are in the past, active span from past due to future
            $dueDate = $status === 'completed'
                ? fake()->dateTimeBetween('-3 months', '-1 week')
                : fake()->dateTimeBetween('-1 week', '+2 weeks');

            $homework = Homework::create([
                'title' => getHomeworkTitle($subject->name, $i),
                'description' => getHomeworkDescription($subject->name),
                'assigned_by' => $teacher->id,
                'grade' => $grade,
                'subject_id' => $subject->id,
                'due_date' => $dueDate,
                'status' => $status,
                'notify_parents' => true,
                'sms_message' => null, // Use default message format
            ]);

            // Create submissions for completed homework
            if ($status === 'completed') {
                // Get students in this grade
                $students = Student::where('grade', $grade)
                                  ->where('enrollment_status', 'active')
                                  ->get();

                // Not all students submit homework
                $submittingStudents = $students->random(min(count($students), rand(3, count($students))));

                foreach ($submittingStudents as $student) {
                    // Some submissions have grades, others don't
                    $hasGrade = fake()->boolean(80); // 80% have grades

                    HomeworkSubmission::create([
                        'homework_id' => $homework->id,
                        'student_id' => $student->id,
                        'content' => getSubmissionContent(),
                        'submitted_at' => fake()->dateTimeBetween($homework->created_at, $homework->due_date),
                        'marks' => $hasGrade ? fake()->numberBetween(50, 100) : null,
                        'feedback' => $hasGrade ? getFeedback() : null,
                    ]);
                }
            }
        }
    }
}

/**
 * Generate realistic homework title
 */
function getHomeworkTitle($subject, $i) {
    $mathTitles = ['Multiplication Practice', 'Division Worksheets', 'Fractions Quiz', 'Geometry Problem Set', 'Number Patterns'];
    $englishTitles = ['Reading Comprehension', 'Grammar Exercises', 'Essay Writing', 'Vocabulary Building', 'Literature Analysis'];
    $scienceTitles = ['Lab Report', 'Scientific Method Quiz', 'Elements Research', 'Biology Diagrams', 'Physics Problem Set'];
    $socialTitles = ['History Timeline', 'Geography Map Work', 'Cultural Studies', 'Current Events Report', 'Civic Education'];

    if (stripos($subject, 'math') !== false) {
        return $mathTitles[array_rand($mathTitles)] . ' #' . $i;
    } elseif (stripos($subject, 'english') !== false) {
        return $englishTitles[array_rand($englishTitles)] . ' #' . $i;
    } elseif (stripos($subject, 'science') !== false) {
        return $scienceTitles[array_rand($scienceTitles)] . ' #' . $i;
    } else {
        return $socialTitles[array_rand($socialTitles)] . ' #' . $i;
    }
}

/**
 * Generate realistic homework description
 */
function getHomeworkDescription($subject) {
    $mathDesc = 'Complete the following problems on a separate sheet of paper. Show all your work and remember to include the correct units in your answers. This assignment will help reinforce the concepts we covered in class today.';

    $englishDesc = 'Read the passage and answer the comprehension questions that follow. Pay attention to the main ideas, supporting details, and the author\'s purpose. Write your answers in complete sentences and be prepared to discuss in class.';

    $scienceDesc = 'Design and conduct a simple experiment following the scientific method. Document your hypothesis, materials, procedure, results, and conclusion. Include at least one diagram or chart to illustrate your findings.';

    $socialDesc = 'Research the assigned topic and prepare a one-page summary. Include at least three reliable sources and be sure to cite them properly. Focus on the key events, people, and significance of the topic.';

    if (stripos($subject, 'math') !== false) {
        return $mathDesc;
    } elseif (stripos($subject, 'english') !== false) {
        return $englishDesc;
    } elseif (stripos($subject, 'science') !== false) {
        return $scienceDesc;
    } else {
        return $socialDesc;
    }
}

/**
 * Generate realistic submission content
 */
function getSubmissionContent() {
    $contents = [
        'I have attached my completed homework assignment. I found questions 3 and 5 particularly challenging but I think I was able to solve them correctly.',
        'Here is my homework submission. I enjoyed working on this assignment and learned a lot about the topic.',
        'Submitting my completed work. I had some questions about the last section that I hope we can discuss in class.',
        'Completed assignment attached. I used the techniques we learned in class and found them very helpful.',
        'My homework submission is complete. I spent extra time on the research portion and found some interesting information.'
    ];

    return $contents[array_rand($contents)];
}

/**
 * Generate realistic teacher feedback
 */
function getFeedback() {
    $feedbacks = [
        'Good work! Your answers demonstrate a clear understanding of the concepts. Keep it up!',
        'Nice job on this assignment. I particularly liked your approach to problem #4. Next time, try to be more detailed in your explanations.',
        'Your work shows improvement, but there are still some areas that need attention. Let\'s review this together during office hours.',
        'Excellent work! You went above and beyond what was required. I\'m impressed with your critical thinking.',
        'Satisfactory completion of the assignment. Make sure to review the concepts from chapter 5 as they\'ll be important for the upcoming test.'
    ];

    return $feedbacks[array_rand($feedbacks)];
}
