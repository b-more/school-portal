<?php

namespace App\Policies;

use App\Models\Homework;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\HandlesAuthorization;

class HomeworkPolicy
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
    public function view(User $user, Homework $homework)
    {
        // Admin can view all homework
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record for this user
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can view homework they created
        if ($homework->assigned_by === $employee->id) {
            return true;
        }

        // Teachers can view homework for their classes and subjects
        if ($employee->role === 'teacher') {
            // Get the classes and subjects assigned to this teacher
            $teacherClasses = $employee->classes()->pluck('id')->toArray();
            $teacherSubjects = $employee->subjects()->pluck('id')->toArray();

            // Get the grade levels for the teacher's classes
            $teacherGrades = \DB::table('classes')
                ->whereIn('id', $teacherClasses)
                ->pluck('grade')
                ->toArray();

            // Teacher can access if the homework is for their grade and subject
            return in_array($homework->grade, $teacherGrades) &&
                   in_array($homework->subject_id, $teacherSubjects);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin can create homework
        if ($user->hasRole('admin')) {
            return true;
        }

        // Teachers can create homework
        $employee = Employee::where('user_id', $user->id)->first();
        return $employee && $employee->role === 'teacher';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Homework $homework)
    {
        // Admin can update any homework
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can only update homework they created
        return $homework->assigned_by === $employee->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Homework $homework)
    {
        // Admin can delete any homework
        if ($user->hasRole('admin')) {
            return true;
        }

        // Get the employee record
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return false;
        }

        // Teachers can only delete homework they created
        return $homework->assigned_by === $employee->id;
    }
}
