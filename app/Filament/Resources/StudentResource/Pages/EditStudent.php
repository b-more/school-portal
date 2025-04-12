<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // No need to modify data before filling the form
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If grade changed but student ID wasn't manually updated, generate new ID
        $originalGrade = $this->record->grade;
        $newGrade = $data['grade'];
        $originalId = $this->record->student_id_number;

        // Only auto-generate ID when:
        // 1. Grade has changed AND
        // 2. Either the student_id_number is empty OR it matches the pattern for the old grade
        if ($originalGrade !== $newGrade) {
            $shouldGenerateNewId = empty($data['student_id_number']);

            // Check if the ID follows the pattern for the old grade
            if (!$shouldGenerateNewId && !empty($originalId)) {
                // Get the prefix for the original grade
                $originalPrefix = $this->getGradePrefix($originalGrade);

                // If the current ID starts with the original grade's prefix, it was auto-generated
                if (strpos($originalId, $originalPrefix) === 0) {
                    $shouldGenerateNewId = true;
                }
            }

            if ($shouldGenerateNewId) {
                // Student is changing grades, so generate new ID
                $data['student_id_number'] = StudentResource::generateStudentId($newGrade);

                // Show notification about ID change
                Notification::make()
                    ->title('Student ID Updated')
                    ->body("Because the grade changed from {$originalGrade} to {$newGrade}, the student ID was updated to {$data['student_id_number']}.")
                    ->success()
                    ->send();
            }
        }

        return $data;
    }

    /**
     * Get the prefix used for student IDs based on grade
     */
    protected function getGradePrefix(string $grade): string
    {
        $gradeMap = [
            'Baby Class' => 'SFBC',
            'Middle Class' => 'SFMC',
            'Reception' => 'SFR',
            'Grade 1' => 'SFG1',
            'Grade 2' => 'SFG2',
            'Grade 3' => 'SFG3',
            'Grade 4' => 'SFG4',
            'Grade 5' => 'SFG5',
            'Grade 6' => 'SFG6',
            'Grade 7' => 'SFG7',
            'Grade 8' => 'SFG8',
            'Grade 9' => 'SFG9',
            'Grade 10' => 'SFG10',
            'Grade 11' => 'SFG11',
            'Grade 12' => 'SFG12',
        ];

        return $gradeMap[$grade] ?? 'SF';
    }
}
