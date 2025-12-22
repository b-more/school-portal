<?php

namespace App\Observers;

use App\Models\Employee;
use App\Notifications\NewEmployeeHired;
use App\Services\AdminNotificationService;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        AdminNotificationService::notifyAdmins(new NewEmployeeHired($employee));
    }
}
