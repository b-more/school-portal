<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class EditStudentFee extends EditRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('printReceipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('student-fees.receipt', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),
        ];
    }

    /**
     * Override the mutate method to check for duplicates before saving
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $studentId = $data['student_id'] ?? null;
        $feeStructureId = $data['fee_structure_id'] ?? null;
        $academicYearId = $data['academic_year_id'] ?? null;
        $termId = $data['term_id'] ?? null;

        $existingFee = $this->getResource()::checkForDuplicateFee(
            $studentId,
            $feeStructureId,
            $academicYearId,
            $termId,
            true,
            $this->record->id
        );

        if ($existingFee) {
            $url = route('filament.admin.resources.student-fees.edit', ['record' => $existingFee->id]);

            Notification::make()
                ->title('Fee Already Assigned')
                ->body('Another fee record already exists for this student with the same term/grade. Please edit that record instead.')
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('edit')
                        ->label('Edit Existing Record')
                        ->url($url)
                        ->openUrlInNewTab(),
                ])
                ->persistent()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
