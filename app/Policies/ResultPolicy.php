<?php

namespace App\Policies;

use App\Models\Result;
use App\Models\User;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResultPolicy
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
    public function view(User $user, Result $result)
    {
        // Admin can view all results
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record for this user
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can view results they recorded
        if ($result->recorded_by === $employee->id) {
            return true;
        }

        // Teachers can view results for homework they created
        if ($result->homework_id && $result->homework && $result->homework->assigned_by === $employee->id) {
            return true;
        }

        // Teachers can view results from students in their classes and for their subjects
        if ($employee->role === 'teacher' && $result->student) {
            // Get teacher's classes
            $teacherClassIds = $employee->classes()->pluck('id')->toArray();

            // Get teacher's subjects
            $teacherSubjectIds = $employee->subjects()->pluck('id')->toArray();

            // Get student's class
            $studentClass = Student::find($result->student_id)->class_id ?? null;

            // Teacher can access if the student is in their class and the result is for their subject
            return $studentClass &&
                   in_array($studentClass, $teacherClassIds) &&
                   in_array($result->subject_id, $teacherSubjectIds);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin can create results
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create results
        $employee = Employee::where('user_id', $user->id)->first();
        return $employee && $employee->role === 'teacher';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Result $result)
    {
        // Admin can update any result
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can only update results they recorded
        if ($result->recorded_by === $employee->id) {
            return true;
        }

        // Teachers can update results for their classes and subjects (if they have override permission)
        if ($employee->role === 'teacher' && $employee->can_override_results && $result->student) {
            // Get teacher's classes
            $teacherClassIds = $employee->classes()->pluck('id')->toArray();

            // Get teacher's subjects
            $teacherSubjectIds = $employee->subjects()->pluck('id')->toArray();

            // Get student's class
            $studentClass = Student::find($result->student_id)->class_id ?? null;

            // Teacher can access if the student is in their class and the result is for their subject
            return $studentClass &&
                   in_array($studentClass, $teacherClassIds) &&
                   in_array($result->subject_id, $teacherSubjectIds);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Result $result)
    {
        // Only admin can delete results
        return $user->hasRole('admin');
    }
}
