<?php

namespace App\Filament\Resources\TeacherHomeworkResource\Pages;

use App\Filament\Resources\TeacherHomeworkResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\Teacher;

class CreateTeacherHomework extends CreateRecord
{
    protected static string $resource = TeacherHomeworkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Get the current teacher
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if ($teacher) {
            $data['assigned_by'] = $teacher->id;
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Convert boolean fields
        $data['allow_late_submission'] = $data['allow_late_submission'] ?? false;
        $data['notify_parents'] = $data['notify_parents'] ?? false;

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->notify_parents) {
            // Send parent notifications
            $this->sendParentNotifications();
        }
    }

    protected function sendParentNotifications(): void
    {
        // This method will be implemented to send SMS to parents
        // For now, we'll just add a notification
        Notification::make()
            ->title('Homework Created')
            ->body('Homework has been created and will be sent to parents.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.teacher-homework.index');
    }
}
