<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use App\Models\FeeStructure;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStudentFee extends CreateRecord
{
    protected static string $resource = StudentFeeResource::class;

    /**
     * Handle form data before creating the record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate auto-incremented receipt number if payment is being made
        if (($data['payment_status'] ?? 'unpaid') !== 'unpaid') {
            $data['receipt_number'] = $this->generateReceiptNumber();
        }

        // Ensure fee structure is properly set and all related fields are populated
        if (isset($data['fee_structure_id'])) {
            $feeStructure = FeeStructure::find($data['fee_structure_id']);

            if ($feeStructure) {
                // Set the academic_year_id, term_id, and grade_id from fee structure if not already set
                $data['academic_year_id'] = $data['academic_year_id'] ?? $feeStructure->academic_year_id;
                $data['term_id'] = $data['term_id'] ?? $feeStructure->term_id;
                $data['grade_id'] = $data['grade_id'] ?? $feeStructure->grade_id;

                // Calculate balance properly
                $amountPaid = (float) ($data['amount_paid'] ?? 0);
                $totalFee = (float) $feeStructure->total_fee;
                $data['balance'] = max(0, $totalFee - $amountPaid);

                // Set payment status based on amount paid
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

    /**
     * Generate a unique sequential receipt number
     */
    protected function generateReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = "RCP-{$year}-";

        // Get the last receipt number for this year
        $lastReceipt = \App\Models\StudentFee::where('receipt_number', 'LIKE', "{$prefix}%")
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            // Extract the number from the last receipt and increment
            $lastNumber = (int) substr($lastReceipt->receipt_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            // First receipt of the year
            $newNumber = 1;
        }

        // Format: RCP-2025-000001
        return $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Handle the record after creation
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Check for duplicate fee record before creation
        if (isset($data['student_id']) && isset($data['fee_structure_id'])) {
            $existingFee = \App\Models\StudentFee::where('student_id', $data['student_id'])
                ->where('fee_structure_id', $data['fee_structure_id'])
                ->first();

            if ($existingFee) {
                // Get the URL to edit the existing fee
                $url = route('filament.admin.resources.student-fees.edit', ['record' => $existingFee->id]);

                \Filament\Notifications\Notification::make()
                    ->title('Duplicate Fee Record')
                    ->body('This student already has a fee for this term/grade. You cannot create a duplicate. Please edit the existing record.')
                    ->danger()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('edit')
                            ->label('Edit Existing Record')
                            ->url($url)
                            ->button()
                            ->color('primary'),
                    ])
                    ->persistent()
                    ->send();

                // Halt the creation process
                $this->halt();
            }
        }

        // Create the record using the mutated data
        $record = static::getModel()::create($data);

        // Load the fee structure relationship immediately after creation
        $record->load(['feeStructure', 'student.parentGuardian']);

        return $record;
    }

    /**
     * Handle actions after record creation
     */
    protected function afterCreate(): void
    {
        // Send SMS notification if requested and payment was made
        if ($this->record->send_sms_notification && $this->record->payment_status !== 'unpaid') {
            try {
                StudentFeeResource::sendPaymentSMS($this->record);

                \Filament\Notifications\Notification::make()
                    ->title('Fee Record Created')
                    ->body('Student fee record created successfully and SMS notification sent.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                // SMS failed but don't fail the creation
                \Illuminate\Support\Facades\Log::error('SMS failed after student fee creation', [
                    'fee_id' => $this->record->id,
                    'error' => $e->getMessage()
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Fee Record Created')
                    ->body('Student fee record created successfully, but SMS notification failed to send.')
                    ->warning()
                    ->send();
            }
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Fee Record Created')
                ->body('Student fee record created successfully.')
                ->success()
                ->send();
        }
    }

    /**
     * Get the redirect URL after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
