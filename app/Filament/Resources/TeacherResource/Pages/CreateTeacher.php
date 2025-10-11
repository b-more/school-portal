<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    /**
     * Prepare data before saving to database
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle the different teacher types
        if (isset($data['teacher_type'])) {
            if ($data['teacher_type'] === 'primary') {
                // For primary teachers - ensure all required fields are set
                $data['is_grade_teacher'] = true;
                $data['is_class_teacher'] = true;
                $data['specialization'] = null; // Primary teachers don't have specialization

                // Ensure grade_id and class_section_id are properly set
                if (! isset($data['grade_id']) || ! $data['grade_id']) {
                    throw new \Exception('Grade is required for primary teachers');
                }

                if (! isset($data['class_section_id']) || ! $data['class_section_id']) {
                    throw new \Exception('Class section is required for primary teachers');
                }
            } elseif ($data['teacher_type'] === 'secondary') {
                // For secondary teachers
                $data['is_class_teacher'] = false;
                $data['class_section_id'] = null; // Secondary teachers aren't assigned to specific class sections initially

                // Only set grade_id if they are a grade teacher
                if (! ($data['is_grade_teacher'] ?? false)) {
                    $data['grade_id'] = null;
                }

                // Ensure specialization is set
                if (! isset($data['specialization']) || ! $data['specialization']) {
                    throw new \Exception('Specialization is required for secondary teachers');
                }
            }
        }

        // Remove form-specific fields before saving
        unset($data['teacher_type']);
        unset($data['subject_classes']);
        unset($data['auto_assigned_subjects']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $teacher = $this->record;
        $data = $this->form->getRawState();

        // Handle primary teachers - assign all grade subjects
        if ($teacher->isPrimaryTeacher() && $teacher->grade_id && $teacher->class_section_id) {
            $this->assignAllSubjectsToGrade($teacher);
        }

        // Handle secondary teachers - assign specific subjects to specific classes
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            $this->handleSecondaryTeacher($teacher, $data);
        }
    }

    /**
     * Assign all subjects to a primary teacher's grade
     */
    private function assignAllSubjectsToGrade($teacher): void
    {
        if (! $teacher->grade || ! $teacher->classSection) {
            return;
        }

        // Update the class section to set this teacher as the class teacher
        $teacher->classSection->update([
            'class_teacher_id' => $teacher->id,
        ]);

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $subjects = $teacher->grade->subjects()->where('is_active', true)->get();

        // Clear existing assignments for this teacher
        $teacher->subjectTeachings()->delete();

        // Assign all subjects
        foreach ($subjects as $subject) {
            $teacher->subjectTeachings()->create([
                'subject_id' => $subject->id,
                'class_section_id' => $teacher->class_section_id,
                'academic_year_id' => $currentAcademicYear?->id,
            ]);
        }

        // Sync the subjects relationship
        $teacher->subjects()->sync($subjects->pluck('id')->toArray());

        Notification::make()
            ->title('Teacher Created Successfully')
            ->body('Assigned to '.$subjects->count().' subjects for '.$teacher->grade->name)
            ->success()
            ->send();
    }

    /**
     * Handle secondary teacher assignment - explicit subject/class assignments
     */
    private function handleSecondaryTeacher($teacher, $data): void
    {
        // Clear existing subject teachings for this teacher
        $teacher->subjectTeachings()->delete();

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $academicYearId = $currentAcademicYear ? $currentAcademicYear->id : null;

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

                $assignedCombinations[] = $subject->name.' ('.$classSection->grade->name.' '.$classSection->name.')';
            }
        }

        // Sync subjects (unique subjects this teacher teaches)
        $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
        $teacher->subjects()->sync($subjectIds);

        // Show success notification
        Notification::make()
            ->title('Secondary Teacher Successfully Created')
            ->body('Assigned to: '.implode(', ', $assignedCombinations))
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
