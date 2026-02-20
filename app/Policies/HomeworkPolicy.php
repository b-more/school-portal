<?php

namespace App\Policies;

use App\Constants\RoleConstants;
use App\Models\Homework;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
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
        // Admin and Secretary can view all homework
        if (in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY])) {
            return true;
        }

        // Students can view homework assigned to their grade
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $homework->grade_id === $student->grade_id) {
                return true;
            }
            return false;
        }

        // Get the teacher record for this user
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            return false;
        }

        // Teachers can view homework they created
        if ($homework->assigned_by === $teacher->id) {
            return true;
        }

        // Teachers can view homework for their classes and subjects
        if ($user->role_id === RoleConstants::TEACHER) {
            // Get the class sections and subjects assigned to this teacher
            $teacherClassSectionIds = $teacher->classSections()->pluck('class_sections.id')->toArray();
            $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

            // Get the grade IDs for the teacher's classes
            $teacherGradeIds = $teacher->classSections()
                ->pluck('class_sections.grade_id')
                ->unique()
                ->toArray();

            // Teacher can access if the homework is for their grade and subject
            return in_array($homework->grade_id, $teacherGradeIds) &&
                in_array($homework->subject_id, $teacherSubjectIds);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin and teachers can create homework
        return in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Homework $homework)
    {
        // Admin can update any homework
        if ($user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Get the teacher record
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            return false;
        }

        // Teachers can only update homework they created
        return $homework->assigned_by === $teacher->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Homework $homework)
    {
        // Admin can delete any homework
        if ($user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Get the teacher record
        $teacher = Teacher::where('user_id', $user->id)->first();

        if (! $teacher) {
            return false;
        }

        // Teachers can only delete homework they created
        return $homework->assigned_by === $teacher->id;
    }
}
