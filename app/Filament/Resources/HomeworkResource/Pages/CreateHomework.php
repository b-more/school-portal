<?php

namespace App\Filament\Resources\HomeworkResource\Pages;

use App\Filament\Resources\HomeworkResource;
use App\Models\Teacher;
use App\Models\AcademicYear;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Constants\RoleConstants;

class CreateHomework extends CreateRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Set assigned_by if it's a teacher
        if ($user && $user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                $data['assigned_by'] = $teacher->id;
            }
        }

        // Set academic year if not set
        if (!isset($data['academic_year_id'])) {
            $currentAcademicYear = AcademicYear::where('is_active', true)->first();
            if ($currentAcademicYear) {
                $data['academic_year_id'] = $currentAcademicYear->id;
            }
        }

        // Ensure status is set
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $homework = $this->record;
        $data = $this->data;

        // Send SMS notifications if enabled
        if (isset($data['notify_parents']) && $data['notify_parents']) {
            try {
                // Use a job to send SMS notifications in the background
                // This prevents the form from hanging if SMS sending takes time
                dispatch(function () use ($homework) {
                    HomeworkResource::sendSmsNotifications($homework);
                })->afterResponse();

                Notification::make()
                    ->title('Homework Created Successfully')
                    ->body('SMS notifications are being sent to parents in the background.')
                    ->success()
                    ->duration(5000)
                    ->send();

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue SMS notifications for homework', [
                    'homework_id' => $homework->id,
                    'error' => $e->getMessage()
                ]);

                Notification::make()
                    ->title('Homework Created')
                    ->body('Homework was created successfully, but there was an issue sending SMS notifications.')
                    ->warning()
                    ->duration(8000)
                    ->send();
            }
        } else {
            Notification::make()
                ->title('Homework Created Successfully')
                ->body('The homework has been uploaded and is now available to students.')
                ->success()
                ->duration(5000)
                ->send();
        }

        // Log homework creation
        \Illuminate\Support\Facades\Log::info('New homework created', [
            'homework_id' => $homework->id,
            'title' => $homework->title,
            'subject_id' => $homework->subject_id,
            'subject_name' => $homework->subject->name,
            'grade_id' => $homework->grade_id,
            'grade_name' => $homework->grade->name,
            'assigned_by' => $homework->assigned_by,
            'teacher_name' => $homework->assignedBy->name,
            'due_date' => $homework->due_date,
            'notify_parents' => $data['notify_parents'] ?? false,
            'created_by_user_id' => Auth::id(),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return null; // We handle notifications in afterCreate()
    }
}
