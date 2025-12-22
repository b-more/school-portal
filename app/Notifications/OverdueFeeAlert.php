<?php

namespace App\Notifications;

use App\Models\StudentFee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OverdueFeeAlert extends Notification
{
    use Queueable;

    public function __construct(
        public StudentFee $studentFee
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->studentFee->student?->name ?? 'Unknown Student';
        $balance = 'K' . number_format($this->studentFee->balance ?? 0, 2);
        $dueDate = $this->studentFee->due_date?->format('M d, Y') ?? 'N/A';

        return [
            'title' => 'Overdue Fee Alert',
            'message' => "{$studentName} has overdue fees of {$balance} (Due: {$dueDate})",
            'student_fee_id' => $this->studentFee->id,
            'student_name' => $studentName,
            'balance' => $this->studentFee->balance,
            'due_date' => $dueDate,
            'icon' => 'heroicon-o-clock',
            'color' => 'danger',
            'url' => "/admin/student-fees/{$this->studentFee->id}",
        ];
    }
}
