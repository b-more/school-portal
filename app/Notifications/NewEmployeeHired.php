<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEmployeeHired extends Notification
{
    use Queueable;

    public function __construct(
        public Employee $employee
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $designation = $this->employee->designation?->name ?? 'Staff';

        return [
            'title' => 'New Employee Hired',
            'message' => "{$this->employee->name} has joined as {$designation}.",
            'employee_id' => $this->employee->id,
            'employee_name' => $this->employee->name,
            'designation' => $designation,
            'icon' => 'heroicon-o-briefcase',
            'color' => 'success',
            'url' => "/admin/employees/{$this->employee->id}",
        ];
    }
}
