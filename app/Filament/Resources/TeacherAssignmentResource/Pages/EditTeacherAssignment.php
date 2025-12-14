<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class EditTeacherAssignment extends EditRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get teacher's specialization to determine type
        $isPrimary = in_array($this->record->specialization, ['Primary', 'ECL']) ||
                     $this->record->specialization === null;

        // Get existing class section assignment
        if ($this->record->class_section_id) {
            $data['class_section_id'] = $this->record->class_section_id;
        }

        // Get existing subject assignments from subject_teachings
        $subjectTeachings = DB::table('subject_teachings')
            ->where('teacher_id', $this->record->id)
            ->get();

        if ($subjectTeachings->isNotEmpty()) {
            $data['subject_assignments'] = $subjectTeachings->pluck('subject_id')->unique()->toArray();
            $data['class_section_assignments'] = $subjectTeachings->pluck('class_section_id')->unique()->toArray();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        if (!$currentAcademicYear) {
            Notification::make()
                ->title('Error')
                ->body('No active academic year found.')
                ->danger()
                ->send();
            return;
        }

        // Clear existing subject teachings for this teacher
        DB::table('subject_teachings')
            ->where('teacher_id', $this->record->id)
            ->delete();

        // Get assigned class section(s)
        $classSectionIds = $this->data['class_section_assignments'] ?? [];

        if (empty($classSectionIds)) {
            // Update teacher's class_section_id to null
            $this->record->update(['class_section_id' => null]);

            Notification::make()
                ->title('Assignments Cleared')
                ->body('Teacher assignments have been cleared.')
                ->success()
                ->send();
            return;
        }

        // Set the first class section as the primary assignment
        $primaryClassSectionId = is_array($classSectionIds) ? $classSectionIds[0] : $classSectionIds;
        $this->record->update(['class_section_id' => $primaryClassSectionId]);

        // Determine if this is a primary teacher
        $classSection = ClassSection::with('grade')->find($primaryClassSectionId);
        $isPrimaryLevel = false;

        if ($classSection && $classSection->grade) {
            $gradeName = $classSection->grade->name;
            // Primary level includes Baby Class, Middle Class, Reception, and Grades 1-7
            $isPrimaryLevel = in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception']) ||
                             (preg_match('/Grade (\d+)/', $gradeName, $matches) && (int)$matches[1] <= 7);
        }

        // Get subjects to assign
        $subjectIds = $this->data['subject_assignments'] ?? [];

        // For primary teachers, auto-assign all primary subjects if none selected
        if ($isPrimaryLevel && empty($subjectIds)) {
            $subjectIds = Subject::where('grade_level', 'Primary')
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        // Create subject teaching records for each class section and subject
        $insertData = [];
        foreach ((array)$classSectionIds as $classSectionId) {
            foreach ($subjectIds as $subjectId) {
                $insertData[] = [
                    'teacher_id' => $this->record->id,
                    'subject_id' => $subjectId,
                    'class_section_id' => $classSectionId,
                    'academic_year_id' => $currentAcademicYear->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            DB::table('subject_teachings')->insert($insertData);
        }

        // Update is_class_teacher status
        $this->record->update(['is_class_teacher' => true]);

        $subjectCount = count($subjectIds);
        $classCount = count((array)$classSectionIds);

        Notification::make()
            ->title('Assignments Saved')
            ->body("Assigned {$subjectCount} subjects across {$classCount} class section(s).")
            ->success()
            ->send();
    }
}
