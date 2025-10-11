<?php

namespace App\Filament\Resources\TeacherHomeworkSubmissionResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\TeacherHomeworkSubmissionResource;
use App\Models\Homework;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacherHomeworkSubmission extends CreateRecord
{
    protected static string $resource = TeacherHomeworkSubmissionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // If student is creating submission, set their student_id
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $data['student_id'] = $student->id;
            }

            // Set default status for student submissions
            $data['status'] = 'submitted';
            $data['submitted_at'] = now();

            // Check if submission is late
            if (isset($data['homework_id'])) {
                $homework = Homework::find($data['homework_id']);
                if ($homework && $homework->submission_end) {
                    $data['is_late'] = now()->isAfter($homework->submission_end);
                }
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Homework submitted successfully')
            ->body('Your homework submission has been received.');
    }
}
