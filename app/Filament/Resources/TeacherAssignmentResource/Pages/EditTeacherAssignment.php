<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditTeacherAssignment extends EditRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get existing class assignments for ECL/Primary
        if (in_array($this->record->department, ['ECL', 'Primary'])) {
            $data['class_assignments'] = DB::table('class_teacher')
                ->where('employee_id', $this->record->id)
                ->pluck('class_id')
                ->toArray();
        }

        // Get existing subject assignments for Secondary
        if ($this->record->department === 'Secondary') {
            // Get assigned subjects
            $data['subject_assignments'] = $this->record->subjects()
                ->pluck('subjects.id')
                ->toArray();

            // Get class-subject assignments
            $classSubjects = DB::table('class_subject_teacher')
                ->where('employee_id', $this->record->id)
                ->get()
                ->groupBy('subject_id');

            $data['class_subject_assignments'] = [];
            foreach ($classSubjects as $subjectId => $classes) {
                $data['class_subject_assignments'][] = [
                    'subject_id' => $subjectId,
                    'class_ids' => $classes->pluck('class_id')->toArray(),
                ];
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Handle class-subject assignments for Secondary teachers
        if ($this->record->department === 'Secondary') {
            // Clear existing class-subject assignments
            DB::table('class_subject_teacher')
                ->where('employee_id', $this->record->id)
                ->delete();

            // Save new class-subject assignments
            if (!empty($this->data['class_subject_assignments'])) {
                foreach ($this->data['class_subject_assignments'] as $assignment) {
                    if (empty($assignment['subject_id']) || empty($assignment['class_ids'])) {
                        continue;
                    }

                    foreach ($assignment['class_ids'] as $classId) {
                        DB::table('class_subject_teacher')->insert([
                            'class_id' => $classId,
                            'subject_id' => $assignment['subject_id'],
                            'employee_id' => $this->record->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        Notification::make()
            ->title('Assignments Saved')
            ->body('Teacher assignments have been updated successfully.')
            ->success()
            ->send();
    }
}
