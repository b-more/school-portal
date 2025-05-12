<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ClassSection;
use App\Models\User;
use App\Constants\RoleConstants;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function afterCreate(): void
    {
        $teacher = $this->record;
        $data = $this->data;

        // Handle primary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'primary' && isset($data['primary_class_section_id'])) {
            // Set the teacher for the assigned class section
            $classSection = ClassSection::find($data['primary_class_section_id']);
            if ($classSection) {
                $classSection->update(['class_teacher_id' => $teacher->id]);
                $teacher->update(['class_section_id' => $data['primary_class_section_id']]);
            }
        }

        // Handle secondary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            // Clear existing subject teachings for this teacher
            $teacher->subjectTeachings()->delete();

            // Add new subject teachings
            foreach ($data['subject_classes'] as $assignment) {
                $teacher->subjectTeachings()->create([
                    'subject_id' => $assignment['subject_id'],
                    'class_section_id' => $assignment['class_section_id'],
                    'academic_year_id' => ClassSection::find($assignment['class_section_id'])->academic_year_id ?? null,
                ]);
            }

            // Sync subjects
            $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
            $teacher->subjects()->sync($subjectIds);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
