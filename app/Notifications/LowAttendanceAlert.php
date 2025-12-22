<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowAttendanceAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Student $student,
        public float $attendanceRate
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $percentage = number_format($this->attendanceRate, 1) . '%';

        return [
            'title' => 'Low Attendance Alert',
            'message' => "{$this->student->name} has low attendance rate: {$percentage}",
            'student_id' => $this->student->id,
            'student_name' => $this->student->name,
            'attendance_rate' => $this->attendanceRate,
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'warning',
            'url' => "/admin/students/{$this->student->id}",
        ];
    }
}
