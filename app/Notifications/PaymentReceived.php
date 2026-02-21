<?php

namespace App\Notifications;

use App\Models\StudentFee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        public StudentFee $studentFee,
        public float $amount
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->studentFee->student?->name ?? 'Unknown Student';
        $formattedAmount = 'K' . number_format($this->amount, 2);

        return [
            'title' => 'Payment Received',
            'message' => "Payment of {$formattedAmount} received from {$studentName}.",
            'student_fee_id' => $this->studentFee->id,
            'student_name' => $studentName,
            'amount' => $this->amount,
            'icon' => 'heroicon-o-banknotes',
            'color' => 'success',
            'url' => "/admin/student-fees/{$this->studentFee->id}",
        ];
    }
}
