<?php

namespace App\Policies;

use App\Constants\RoleConstants;
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
        if ($user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teachers can view all results
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        // Students can view their own results
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student && $result->student_id === $student->id) {
                return true;
            }
        }

        // Parents can view their children's results
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;
            if ($parent) {
                $childrenIds = $parent->students()->pluck('id')->toArray();
                if (in_array($result->student_id, $childrenIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin can create results
        if ($user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teachers can create results
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Result $result)
    {
        // Admin can update any result
        if ($user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teachers can update results
        if (in_array($user->role_id, RoleConstants::teaching())) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Result $result)
    {
        // Only admin can delete results
        return $user->hasRole('admin') || $user->role_id === RoleConstants::ADMIN;
    }
}
