<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\ParentGuardian;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateStudentFee extends CreateRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate the balance based on the fee structure
        if (isset($data['fee_structure_id'])) {
            $feeStructure = FeeStructure::find($data['fee_structure_id']);
            if ($feeStructure) {
                $amountPaid = (float) ($data['amount_paid'] ?? 0);
                $totalFee = (float) $feeStructure->total_fee;

                // Set the balance
                $data['balance'] = max(0, $totalFee - $amountPaid);

                // Set the payment status based on the amount paid
                if ($amountPaid <= 0) {
                    $data['payment_status'] = 'unpaid';
                } elseif ($amountPaid >= $totalFee) {
                    $data['payment_status'] = 'paid';
                    $data['balance'] = 0;
                } else {
                    $data['payment_status'] = 'partial';
                }
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Get the newly created record
        $studentFee = $this->record;

        // Only send SMS if payment was made (not if status is 'unpaid')
        if ($studentFee->payment_status !== 'unpaid') {
            $this->sendPaymentNotification($studentFee);
        }
    }

    protected function sendPaymentNotification(Model $studentFee): void
    {
        // Get the student
        $student = Student::find($studentFee->student_id);

        if (!$student || !$student->parent_guardian_id) {
            Notification::make()
                ->title('SMS Not Sent')
                ->body('No parent/guardian found for this student.')
                ->warning()
                ->send();
            return;
        }

        // Get the parent/guardian
        $parentGuardian = ParentGuardian::find($student->parent_guardian_id);

        if (!$parentGuardian || !$parentGuardian->phone) {
            Notification::make()
                ->title('SMS Not Sent')
                ->body('No phone number found for the parent/guardian.')
                ->warning()
                ->send();
            return;
        }

        try {
            // Format the message with payment details
            $message = "Dear {$parentGuardian->name}, thank you for your payment of ZMW {$studentFee->amount_paid} for {$student->name}'s fees. ";

            // Add fee structure details
            if ($studentFee->feeStructure) {
                $message .= "Grade: {$studentFee->feeStructure->grade}, Term: {$studentFee->feeStructure->term}. ";
            }

            // Add payment status
            $message .= "Total fee: ZMW {$studentFee->feeStructure->total_fee}, Balance: ZMW {$studentFee->balance}. ";

            if ($studentFee->payment_status === 'paid') {
                $message .= "Status: FULLY PAID. Thank you!";
            } else {
                $message .= "Status: PARTIALLY PAID. Receipt No: {$studentFee->receipt_number}.";
            }

            // Format phone number to ensure it has the country code
            $phoneNumber = static::formatPhoneNumber($parentGuardian->phone);

            // Send the SMS
            static::sendMessage($message, $phoneNumber);

            // Log successful SMS
            Log::info('Fee payment notification sent', [
                'student_fee_id' => $studentFee->id,
                'student_id' => $student->id,
                'parent_id' => $parentGuardian->id,
                'amount' => $studentFee->amount_paid,
                'receipt' => $studentFee->receipt_number
            ]);

            // Show success notification
            Notification::make()
                ->title('Payment Notification Sent')
                ->body("SMS notification sent to {$parentGuardian->name} at {$phoneNumber}.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to send payment notification', [
                'student_fee_id' => $studentFee->id,
                'student_id' => $student->id,
                'parent_id' => $parentGuardian->id,
                'error' => $e->getMessage()
            ]);

            // Show error notification
            Notification::make()
                ->title('SMS Notification Failed')
                ->body("Could not send payment notification: {$e->getMessage()}")
                ->danger()
                ->send();
        }
    }

    /**
     * Format phone number to ensure it has the country code
     */
    protected static function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if number already has country code (260 for Zambia)
        if (substr($phoneNumber, 0, 3) === '260') {
            // Number already has country code
            return $phoneNumber;
        }

        // If starting with 0, replace with country code
        if (substr($phoneNumber, 0, 1) === '0') {
            return '260' . substr($phoneNumber, 1);
        }

        // If number doesn't have country code, add it
        if (strlen($phoneNumber) === 9) {
            return '260' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Send a message via SMS
     */
    protected static function sendMessage($message_string, $phone_number)
    {
        try {
            // Log the sending attempt
            Log::info('Sending fee payment SMS notification', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            $url_encoded_message = urlencode($message_string);

            $sendSenderSMS = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

            // Log the response
            Log::info('SMS API Response for fee payment', [
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
            ]);

            return $sendSenderSMS->successful();
        } catch (\Exception $e) {
            Log::error('Fee payment SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $phone_number,
            ]);
            throw $e; // Re-throw to be caught by the calling method
        }
    }
}
