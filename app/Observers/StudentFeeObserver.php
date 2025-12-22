<?php

namespace App\Observers;

use App\Models\StudentFee;
use App\Notifications\PaymentReceived;
use App\Notifications\OverdueFeeAlert;
use App\Services\AdminNotificationService;

class StudentFeeObserver
{
    /**
     * Handle the StudentFee "updated" event.
     */
    public function updated(StudentFee $studentFee): void
    {
        // Check if a payment was made (amount_paid increased)
        if ($studentFee->wasChanged('amount_paid')) {
            $previousAmount = $studentFee->getOriginal('amount_paid') ?? 0;
            $currentAmount = $studentFee->amount_paid ?? 0;
            $paymentAmount = $currentAmount - $previousAmount;

            if ($paymentAmount > 0) {
                AdminNotificationService::notifyAdmins(new PaymentReceived($studentFee, $paymentAmount));
            }
        }

        // Check for overdue fees
        if ($studentFee->wasChanged('status') && $studentFee->status === 'overdue') {
            AdminNotificationService::notifyAdmins(new OverdueFeeAlert($studentFee));
        }
    }
}
