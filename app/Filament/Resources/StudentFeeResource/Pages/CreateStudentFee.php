<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Actions;

class CreateStudentFee extends CreateRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Override the mutate method to check for duplicates before saving
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $studentId = $data['student_id'] ?? null;
        $feeStructureId = $data['fee_structure_id'] ?? null;
        $academicYearId = $data['academic_year_id'] ?? null;
        $termId = $data['term_id'] ?? null;

        $existingFee = $this->getResource()::checkForDuplicateFee(
            $studentId,
            $feeStructureId,
            $academicYearId,
            $termId
        );

        if ($existingFee) {
            $url = route('filament.admin.resources.student-fees.edit', ['record' => $existingFee->id]);

            Notification::make()
                ->title('Fee Already Assigned')
                ->body('This student already has fees assigned for this term/grade. Please edit the existing record instead.')
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
