<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\EditRecord;
use App\Models\ClassSection;
use App\Models\User;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $teacher = $this->record;
        $data = $this->data;

        // Handle primary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'primary' && isset($data['primary_class_section_id'])) {
            $this->handlePrimaryTeacher($teacher, $data);
        }

        // Handle secondary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            $this->handleSecondaryTeacher($teacher, $data);
        }
    }

    /**
     * Handle primary teacher assignment - automatically assign all grade subjects
     */
    private function handlePrimaryTeacher($teacher, $data): void
    {
        $classSection = ClassSection::find($data['primary_class_section_id']);

        if (!$classSection) {
            Notification::make()
                ->title('Error: Class section not found')
                ->danger()
                ->send();
            return;
        }

        // Update class section with teacher
        $classSection->update(['class_teacher_id' => $teacher->id]);
        $teacher->update(['class_section_id' => $data['primary_class_section_id']]);

        // Get the current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            Notification::make()
                ->title('Warning: No active academic year found')
                ->warning()
                ->send();
            return;
        }

        // Get all subjects for this grade
        $grade = $classSection->grade;
        if (!$grade) {
            Notification::make()
                ->title('Error: Grade not found for class section')
                ->danger()
                ->send();
            return;
        }

        // Check if class section or grade changed - if so, reassign subjects
        $existingTeachings = $teacher->subjectTeachings()
            ->where('class_section_id', $classSection->id)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->count();

        $gradeSubjects = $grade->subjects()->where('is_active', true)->get();

        // Only reassign if number of subjects doesn't match or if it's a new assignment
        if ($existingTeachings !== $gradeSubjects->count() || $existingTeachings === 0) {

            // Clear existing subject teachings for this teacher in current academic year
            $teacher->subjectTeachings()
                ->where('academic_year_id', $currentAcademicYear->id)
                ->delete();

            if ($gradeSubjects->isEmpty()) {
                Notification::make()
                    ->title('Warning: No subjects found for grade ' . $grade->name)
                    ->body('Please assign subjects to this grade first.')
                    ->warning()
                    ->send();
                return;
            }

            // Create subject teaching records for each subject
            $assignedSubjects = [];
            foreach ($gradeSubjects as $subject) {
                $teacher->subjectTeachings()->create([
                    'subject_id' => $subject->id,
                    'class_section_id' => $classSection->id,
                    'academic_year_id' => $currentAcademicYear->id,
                ]);
                $assignedSubjects[] = $subject->name;
            }

            // Sync the subjects relationship
            $subjectIds = $gradeSubjects->pluck('id')->toArray();
            $teacher->subjects()->sync($subjectIds);

            Notification::make()
                ->title('Primary Teacher Updated')
                ->body('Reassigned to all subjects: ' . implode(', ', $assignedSubjects))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Primary Teacher Updated')
                ->body('Subject assignments remain unchanged')
                ->success()
                ->send();
        }
    }

    /**
     * Handle secondary teacher assignment - explicit subject/class assignments
     */
    private function handleSecondaryTeacher($teacher, $data): void
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

        // Clear existing subject teachings for this teacher in current academic year
        $teacher->subjectTeachings()
            ->where('academic_year_id', $academicYearId)
            ->delete();

        $assignedCombinations = [];

        // Add new subject teachings
        foreach ($data['subject_classes'] as $assignment) {
            $classSection = ClassSection::find($assignment['class_section_id']);
            $subject = \App\Models\Subject::find($assignment['subject_id']);

            if ($classSection && $subject) {
                $teacher->subjectTeachings()->create([
                    'subject_id' => $assignment['subject_id'],
                    'class_section_id' => $assignment['class_section_id'],
                    'academic_year_id' => $academicYearId,
                ]);

                $assignedCombinations[] = $subject->name . ' (' . $classSection->grade->name . ' ' . $classSection->name . ')';
            }
        }

        // Sync subjects (unique subjects this teacher teaches)
        $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
        $teacher->subjects()->sync($subjectIds);

        Notification::make()
            ->title('Secondary Teacher Updated')
            ->body('Updated assignments: ' . implode(', ', $assignedCombinations))
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
