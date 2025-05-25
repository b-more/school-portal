<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use App\Models\FeeStructure;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentFee extends EditRecord
{
    protected static string $resource = StudentFeeResource::class;

    /**
     * Handle form data before saving the record
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
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
     * Handle actions after record update
     */
    protected function afterSave(): void
    {
        // Reload the fee structure relationship after save
        $this->record->load(['feeStructure', 'student.parentGuardian']);

        // Send SMS notification if requested, payment status changed, and payment was made
        if ($this->record->send_sms_notification &&
            $this->record->payment_status !== 'unpaid' &&
            $this->record->isDirty(['amount_paid', 'payment_status'])) {

            try {
                StudentFeeResource::sendPaymentSMS($this->record);

                \Filament\Notifications\Notification::make()
                    ->title('Fee Record Updated')
                    ->body('Student fee record updated successfully and SMS notification sent.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                // SMS failed but don't fail the update
                \Illuminate\Support\Facades\Log::error('SMS failed after student fee update', [
                    'fee_id' => $this->record->id,
                    'error' => $e->getMessage()
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Fee Record Updated')
                    ->body('Student fee record updated successfully, but SMS notification failed to send.')
                    ->warning()
                    ->send();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('generateReceipt')
                ->label('Generate Receipt')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('student-fees.receipt', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),
        ];
    }

    /**
     * Get the redirect URL after update
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
