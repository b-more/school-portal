<?php

namespace App\Observers;

use App\Models\Student;
use App\Notifications\NewStudentRegistered;
use App\Services\AdminNotificationService;

class StudentObserver
{
    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student): void
    {
        AdminNotificationService::notifyAdmins(new NewStudentRegistered($student));
    }
}
