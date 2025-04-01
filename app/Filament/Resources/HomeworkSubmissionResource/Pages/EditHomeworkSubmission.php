<?php

namespace App\Filament\Resources\HomeworkSubmissionResource\Pages;

use App\Filament\Resources\HomeworkSubmissionResource;
use App\Models\Result;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHomeworkSubmission extends EditRecord
{
    protected static string $resource = HomeworkSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('createResult')
                ->label('Create Result Record')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->action(function () {
                    // Only proceed if the submission has been graded
                    if ($this->record->marks === null) {
                        Notification::make()
                            ->title('Cannot Create Result')
                            ->body('The submission must be graded before creating a result record.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Check if result already exists
                    $existingResult = Result::where('student_id', $this->record->student_id)
                        ->where('exam_type', 'assignment')
                        ->where('homework_id', $this->record->homework_id)
                        ->first();

                    if ($existingResult) {
                        Notification::make()
                            ->title('Result Already Exists')
                            ->body('A result record for this homework assignment already exists.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Get homework and student details
                    $homework = $this->record->homework;
                    $student = $this->record->student;

                    if (!$homework || !$student) {
                        Notification::make()
                            ->title('Missing Information')
                            ->body('Cannot create result due to missing homework or student information.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Create corresponding result record
                    $result = Result::create([
                        'student_id' => $student->id,
                        'subject_id' => $homework->subject_id,
                        'exam_type' => 'assignment',
                        'homework_id' => $homework->id,
                        'marks' => $this->record->marks,
                        'grade' => $this->getGradeFromMarks($this->record->marks),
                        'term' => 'first', // Default - you may want to set this dynamically
                        'year' => date('Y'),
                        'comment' => $this->record->feedback,
                        'recorded_by' => $this->record->graded_by ?? auth()->id(),
                        'notify_parent' => true,
                    ]);

                    // Redirect to the result edit page
                    Notification::make()
                        ->title('Result Created')
                        ->body('Result record has been created successfully.')
                        ->success()
                        ->send();

                    // Redirect to the result edit page
                    $this->redirect(route('filament.admin.resources.results.edit', ['record' => $result->id]));
                })
                ->visible(fn () =>
                    $this->record->marks !== null &&
                    !Result::where('student_id', $this->record->student_id)
                        ->where('exam_type', 'assignment')
                        ->where('homework_id', $this->record->homework_id)
                        ->exists()
                ),
        ];
    }

    /**
     * Determine letter grade from numerical marks
     */
    protected function getGradeFromMarks($marks) {
        if ($marks >= 90) return 'A+';
        if ($marks >= 80) return 'A';
        if ($marks >= 70) return 'B';
        if ($marks >= 60) return 'C';
        if ($marks >= 50) return 'D';
        return 'F';
    }
}
