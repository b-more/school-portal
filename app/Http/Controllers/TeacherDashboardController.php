<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Result;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
    /**
     * Display the teacher dashboard.
     */
    public function index()
    {
        $teacher = $this->getTeacherProfile();

        $totalAssignedClasses = $teacher->classes()->count();
        $totalStudents = $this->getTeacherStudentsQuery()->count();
        $totalSubjects = $teacher->subjects()->count();
        $recentHomework = $this->getHomeworkQuery()->latest()->take(5)->get();
        $pendingSubmissions = HomeworkSubmission::whereIn('homework_id', $this->getHomeworkQuery()->pluck('id'))
            ->where('status', 'submitted')
            ->count();

        return view('teacher.dashboard', compact(
            'teacher',
            'totalAssignedClasses',
            'totalStudents',
            'totalSubjects',
            'recentHomework',
            'pendingSubmissions'
        ));
    }

    /**
     * Display the list of classes assigned to the teacher.
     */
    public function classes()
    {
        $teacher = $this->getTeacherProfile();
        $classes = $teacher->classes()->with('teachers')->get();

        return view('teacher.classes', compact('teacher', 'classes'));
    }

    /**
     * Display students in a specific class or all assigned classes.
     */
    public function students(Request $request)
    {
        $teacher = $this->getTeacherProfile();
        $classId = $request->query('class_id');

        $classes = $teacher->classes()->pluck('name', 'id');

        $studentsQuery = $this->getTeacherStudentsQuery();

        if ($classId) {
            $studentsQuery->where('students.class_id', $classId);
        }

        $students = $studentsQuery->paginate(20);

        return view('teacher.students', compact('teacher', 'classes', 'students', 'classId'));
    }

    /**
     * Display, create, and manage homework for the teacher's classes.
     */
    public function homework()
    {
        $teacher = $this->getTeacherProfile();
        $homework = $this->getHomeworkQuery()->paginate(15);

        $classes = $teacher->classes()->pluck('name', 'id');
        $subjects = $teacher->subjects()->pluck('name', 'id');

        return view('teacher.homework', compact('teacher', 'homework', 'classes', 'subjects'));
    }

    /**
     * Create a new homework assignment.
     */
    public function createHomework(Request $request)
    {
        $teacher = $this->getTeacherProfile();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'grade' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date',
            'submission_start' => 'required|date',
            'submission_end' => 'required|date|after:submission_start',
            'allow_late_submission' => 'boolean',
            'late_submission_deadline' => 'nullable|date|after:submission_end',
            'homework_file' => 'nullable|file|max:10240',
            'file_attachment' => 'nullable|file|max:10240',
            'max_score' => 'required|numeric|min:1',
            'submission_instructions' => 'nullable|string',
            'notify_parents' => 'boolean',
            'sms_message' => 'nullable|string|max:160',
        ]);

        // Make sure the teacher has access to the selected subject
        if (!$teacher->subjects()->where('subjects.id', $validated['subject_id'])->exists()) {
            return back()->with('error', 'You are not authorized to create homework for this subject.');
        }

        // Handle file uploads
        if ($request->hasFile('homework_file')) {
            $validated['homework_file'] = $request->file('homework_file')->store('homework-files');
        }

        if ($request->hasFile('file_attachment')) {
            $validated['file_attachment'] = $request->file('file_attachment')->store('homework-resources');
        }

        // Add the teacher as the creator
        $validated['assigned_by'] = $teacher->id;
        $validated['status'] = 'active';

        // Create the homework
        $homework = Homework::create($validated);

        return redirect()->route('teacher.homework')->with('success', 'Homework assignment created successfully.');
    }

    /**
     * Display homework submissions for the teacher to review.
     */
    public function submissions(Request $request)
    {
        $teacher = $this->getTeacherProfile();
        $homeworkId = $request->query('homework_id');

        // Get all homework assigned by this teacher
        $homeworkList = $this->getHomeworkQuery()->pluck('title', 'id');

        $submissionsQuery = HomeworkSubmission::whereIn('homework_id', $this->getHomeworkQuery()->pluck('id'))
            ->with(['student', 'homework']);

        if ($homeworkId) {
            $submissionsQuery->where('homework_id', $homeworkId);
        }

        $submissions = $submissionsQuery->latest('submitted_at')->paginate(20);

        return view('teacher.submissions', compact('teacher', 'submissions', 'homeworkList', 'homeworkId'));
    }

    /**
     * Grade a homework submission.
     */
    public function gradeSubmission(Request $request, HomeworkSubmission $submission)
    {
        $teacher = $this->getTeacherProfile();

        // Verify the teacher has access to this submission
        if (!$this->canAccessSubmission($teacher, $submission)) {
            return back()->with('error', 'You are not authorized to grade this submission.');
        }

        $validated = $request->validate([
            'marks' => 'required|numeric|min:0',
            'feedback' => 'required|string',
            'teacher_notes' => 'nullable|string',
        ]);

        // Ensure marks don't exceed max score
        $maxScore = $submission->homework->max_score ?? 100;
        if ($validated['marks'] > $maxScore) {
            return back()->with('error', "Marks cannot exceed the maximum score of {$maxScore}.");
        }

        // Update the submission
        $submission->update([
            'marks' => $validated['marks'],
            'feedback' => $validated['feedback'],
            'teacher_notes' => $validated['teacher_notes'],
            'status' => 'graded',
            'graded_by' => $teacher->id,
            'graded_at' => now(),
        ]);

        return back()->with('success', 'Submission graded successfully.');
    }

    /**
     * Create a result record for a graded submission.
     */
    public function createResult(Request $request, HomeworkSubmission $submission)
    {
        $teacher = $this->getTeacherProfile();

        // Verify the teacher has access to this submission
        if (!$this->canAccessSubmission($teacher, $submission)) {
            return back()->with('error', 'You are not authorized to create a result for this submission.');
        }

        // Verify the submission has been graded
        if ($submission->marks === null) {
            return back()->with('error', 'The submission must be graded before creating a result record.');
        }

        // Check if result already exists
        $existingResult = Result::where('student_id', $submission->student_id)
            ->where('exam_type', 'assignment')
            ->where('homework_id', $submission->homework_id)
            ->first();

        if ($existingResult) {
            return back()->with('error', 'A result record for this homework assignment already exists.');
        }

        // Get homework and student details
        $homework = $submission->homework;
        $student = $submission->student;

        if (!$homework || !$student) {
            return back()->with('error', 'Cannot create result due to missing homework or student information.');
        }

        // Determine letter grade from marks
        $grade = $this->getGradeFromMarks($submission->marks);

        // Create the result record
        $result = Result::create([
            'student_id' => $student->id,
            'subject_id' => $homework->subject_id,
            'exam_type' => 'assignment',
            'homework_id' => $homework->id,
            'marks' => $submission->marks,
            'grade' => $grade,
            'term' => 'first', // You might want to make this dynamic
            'year' => date('Y'),
            'comment' => $submission->feedback,
            'recorded_by' => $teacher->id,
            'notify_parent' => $request->input('notify_parent', true),
        ]);

        return back()->with('success', 'Result record created successfully.');
    }

    /**
     * Display and manage results for the teacher's students.
     */
    public function results(Request $request)
    {
        $teacher = $this->getTeacherProfile();
        $subjectId = $request->query('subject_id');
        $examType = $request->query('exam_type');

        // Get subjects taught by this teacher
        $subjects = $teacher->subjects()->pluck('name', 'id');

        // Get student IDs from teacher's classes
        $studentIds = $this->getTeacherStudentsQuery()->pluck('students.id');

        $resultsQuery = Result::whereIn('student_id', $studentIds)
            ->with(['student', 'subject', 'homework']);

        if ($subjectId) {
            $resultsQuery->where('subject_id', $subjectId);
        }

        if ($examType) {
            $resultsQuery->where('exam_type', $examType);
        }

        $results = $resultsQuery->latest()->paginate(20);

        $examTypes = [
            'mid-term' => 'Mid-Term',
            'final' => 'Final',
            'quiz' => 'Quiz',
            'assignment' => 'Assignment',
        ];

        return view('teacher.results', compact('teacher', 'results', 'subjects', 'examTypes', 'subjectId', 'examType'));
    }

    /**
     * Create a new result record directly (not from a submission).
     */
    public function createDirectResult(Request $request)
    {
        $teacher = $this->getTeacherProfile();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|string',
            'homework_id' => 'nullable|exists:homework,id',
            'marks' => 'required|numeric|min:0|max:100',
            'term' => 'required|string',
            'year' => 'required|integer',
            'comment' => 'nullable|string',
            'notify_parent' => 'boolean',
            'sms_message' => 'nullable|string|max:160',
        ]);

        // Verify teacher has access to this student and subject
        $studentIds = $this->getTeacherStudentsQuery()->pluck('students.id');
        if (!$studentIds->contains($validated['student_id'])) {
            return back()->with('error', 'You are not authorized to create results for this student.');
        }

        if (!$teacher->subjects()->where('subjects.id', $validated['subject_id'])->exists()) {
            return back()->with('error', 'You are not authorized to create results for this subject.');
        }

        // Determine letter grade from marks
        $validated['grade'] = $this->getGradeFromMarks($validated['marks']);

        // Add the teacher as the recorder
        $validated['recorded_by'] = $teacher->id;

        // Create the result
        $result = Result::create($validated);

        return redirect()->route('teacher.results')->with('success', 'Result created successfully.');
    }

    /**
     * Helper method to get the current teacher's profile.
     */
    protected function getTeacherProfile()
    {
        $userId = Auth::id();
        return Employee::where('user_id', $userId)->firstOrFail();
    }

    /**
     * Helper method to get students associated with the teacher's classes.
     */
    protected function getTeacherStudentsQuery()
    {
        $teacher = $this->getTeacherProfile();

        // Get all class IDs assigned to this teacher
        $classIds = $teacher->classes()->pluck('id');

        // Return query for students in these classes
        return Student::whereIn('class_id', $classIds);
    }

    /**
     * Helper method to get homework assigned by this teacher.
     */
    protected function getHomeworkQuery()
    {
        $teacher = $this->getTeacherProfile();
        return Homework::where('assigned_by', $teacher->id);
    }

    /**
     * Determine if a teacher can access a specific submission.
     */
    protected function canAccessSubmission(Employee $teacher, HomeworkSubmission $submission)
    {
        // Teacher can access if they created the homework
        if ($submission->homework && $submission->homework->assigned_by === $teacher->id) {
            return true;
        }

        // Teacher can access if student is in their class and subject matches
        if ($submission->student && $submission->homework) {
            $studentIds = $this->getTeacherStudentsQuery()->pluck('id');
            $subjectIds = $teacher->subjects()->pluck('subjects.id');

            return $studentIds->contains($submission->student_id) &&
                  $subjectIds->contains($submission->homework->subject_id);
        }

        return false;
    }

    /**
     * Determine letter grade from numerical marks.
     */
    protected function getGradeFromMarks($marks) {
        if ($marks >= 90) return 'A+';
        if ($marks >= 80) return 'A';
        if ($marks >= 70) return 'B';
        if ($marks >= 60) return 'C';
        if ($marks >= 50) return 'D';
        return 'F';
    }
}
