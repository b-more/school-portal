<?php

namespace App\Policies;

use App\Constants\RoleConstants;
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
        // Admin and Secretary can view all submissions
        if ($user->hasRole('admin') || in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY])) {
            return true;
        }

        // Teachers can view all submissions
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        // Students can view their own submissions
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $submission->student_id === $student->id) {
                return true;
            }
            return false;
        }

        // Parents can view their children's submissions
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;
            if ($parent) {
                $childrenIds = $parent->students()->pluck('id')->toArray();
                if (in_array($submission->student_id, $childrenIds)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin can create submissions
        if ($user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teachers can create submissions on behalf of students
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        // Students can create their own submissions
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            // Student must exist and be actively enrolled
            return $student && $student->enrollment_status === 'active';
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, HomeworkSubmission $submission)
    {
        // Admin can update any submission
        if ($user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teachers can update any submission
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        // Students can update their own submissions if not yet graded
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $submission->student_id === $student->id) {
                // Only allow updates if submission hasn't been graded yet
                return $submission->status !== 'graded' && $submission->marks === null;
            }
            return false;
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
