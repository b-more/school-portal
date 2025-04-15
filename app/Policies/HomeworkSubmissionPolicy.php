<?php

namespace App\Policies;

use App\Models\HomeworkSubmission;
use App\Models\User;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeworkSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user)
    {
        return true; // Everyone can see the list, but it will be filtered
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, HomeworkSubmission $submission)
    {
        // Admin can view all submissions
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record for this user
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can view submissions they graded
        if ($submission->graded_by === $employee->id) {
            return true;
        }

        // Teachers can view submissions for homework they created
        if ($submission->homework && $submission->homework->assigned_by === $employee->id) {
            return true;
        }

        // Teachers can view submissions from students in their classes and for their subjects
        if ($employee->role === 'teacher' && $submission->student && $submission->homework) {
            // Get teacher's classes
            $teacherClassIds = $employee->classes()->pluck('id')->toArray();

            // Get teacher's subjects
            $teacherSubjectIds = $employee->subjects()->pluck('id')->toArray();

            // Get student's class
            $studentClass = Student::find($submission->student_id)->class_id ?? null;

            // Get homework subject
            $homeworkSubject = $submission->homework->subject_id ?? null;

            // Teacher can access if the student is in their class and the homework is for their subject
            return $studentClass && $homeworkSubject &&
                   in_array($studentClass, $teacherClassIds) &&
                   in_array($homeworkSubject, $teacherSubjectIds);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin can create submissions
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create submissions on behalf of students
        $employee = Employee::where('user_id', $user->id)->first();
        return $employee && $employee->role === 'teacher';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HomeworkSubmission $submission)
    {
        // Admin can update any submission
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can update submissions they graded
        if ($submission->graded_by === $employee->id) {
            return true;
        }

        // Teachers can update submissions for homework they created
        if ($submission->homework && $submission->homework->assigned_by === $employee->id) {
            return true;
        }

        // Teachers can update submissions from students in their classes and for their subjects
        if ($employee->role === 'teacher' && $submission->student && $submission->homework) {
            // Get teacher's classes
            $teacherClassIds = $employee->classes()->pluck('id')->toArray();

            // Get teacher's subjects
            $teacherSubjectIds = $employee->subjects()->pluck('id')->toArray();

            // Get student's class
            $studentClass = Student::find($submission->student_id)->class_id ?? null;

            // Get homework subject
            $homeworkSubject = $submission->homework->subject_id ?? null;

            // Teacher can access if the student is in their class and the homework is for their subject
            return $studentClass && $homeworkSubject &&
                   in_array($studentClass, $teacherClassIds) &&
                   in_array($homeworkSubject, $teacherSubjectIds);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, HomeworkSubmission $submission)
    {
        // Only admin can delete submissions
        return $user->hasRole('admin');
    }
}
