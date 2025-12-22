<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewStudentRegistered extends Notification
{
    use Queueable;

    public function __construct(
        public Student $student
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Student Registered',
            'message' => "A new student '{$this->student->name}' has been registered.",
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'icon' => 'heroicon-o-user-plus',
            'color' => 'success',
            'url' => "/admin/students/{$this->student->id}",
        ];
    }
}
