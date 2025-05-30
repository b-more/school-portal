<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ClassSection;
use App\Models\User;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
use Filament\Notifications\Notification;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function afterCreate(): void
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
     * Handle primary teacher assignment - automatically assign all PRIMARY LEVEL subjects only
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

        // Get the grade
        $grade = $classSection->grade;
        if (!$grade) {
            Notification::make()
                ->title('Error: Grade not found for class section')
                ->danger()
                ->send();
            return;
        }

        // Get ONLY PRIMARY LEVEL subjects for this grade
        $primarySubjects = $grade->subjects()
            ->where('is_active', true)
            ->where(function($query) {
                $query->where('grade_level', 'Primary')
                      ->orWhere('name', 'like', '%(Primary)%')
                      ->orWhereIn('code', [
                          'ENGP', 'MATP', 'SCIP', 'SOCP', 'CTSP', 'ZAMP',
                          'PHEP', 'RELP', 'ARTP', 'MUSP'
                      ]);
            })
            ->get();

        if ($primarySubjects->isEmpty()) {
            // If no primary subjects found, get subjects by checking if they're appropriate for primary grades
            $primaryGradeNames = ['Baby Class', 'Middle Class', 'Reception', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7'];

            if (in_array($grade->name, $primaryGradeNames)) {
                // Get subjects that are typically for primary level
                $primarySubjects = $grade->subjects()
                    ->where('is_active', true)
                    ->whereNotIn('name', [
                        // Exclude clearly secondary subjects
                        'Biology', 'Chemistry', 'Physics', 'Accounting',
                        'Business Studies', 'Commerce', 'Agriculture',
                        'French', 'Technical Drawing'
                    ])
                    ->get();
            }
        }

        if ($primarySubjects->isEmpty()) {
            Notification::make()
                ->title('Warning: No primary subjects found for grade ' . $grade->name)
                ->body('Please ensure primary subjects are properly assigned to this grade.')
                ->warning()
                ->send();
            return;
        }

        // Clear any existing subject teachings for this teacher
        $teacher->subjectTeachings()->delete();

        // Create subject teaching records for each PRIMARY subject only
        $assignedSubjects = [];
        foreach ($primarySubjects as $subject) {
            $teacher->subjectTeachings()->create([
                'subject_id' => $subject->id,
                'class_section_id' => $classSection->id,
                'academic_year_id' => $currentAcademicYear->id,
            ]);
            $assignedSubjects[] = $subject->name;
        }

        // Sync the subjects relationship (for quick access)
        $subjectIds = $primarySubjects->pluck('id')->toArray();
        $teacher->subjects()->sync($subjectIds);

        // Show success notification
        Notification::make()
            ->title('Primary Teacher Successfully Created')
            ->body('Automatically assigned to primary subjects only: ' . implode(', ', $assignedSubjects))
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

                $assignedCombinations[] = $subject->name . ' (' . $classSection->grade->name . ' ' . $classSection->name . ')';
            }
        }

        // Sync subjects (unique subjects this teacher teaches)
        $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
        $teacher->subjects()->sync($subjectIds);

        // Show success notification
        Notification::make()
            ->title('Secondary Teacher Successfully Created')
            ->body('Assigned to: ' . implode(', ', $assignedCombinations))
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
