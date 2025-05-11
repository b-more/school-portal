<?php

namespace App\Filament\Resources\HomeworkResource\Pages;

use App\Filament\Resources\HomeworkResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateHomework extends CreateRecord
{
    protected static string $resource = HomeworkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default submission start and end dates based on due date
        if (isset($data['due_date'])) {
            $dueDate = \Carbon\Carbon::parse($data['due_date']);
            $data['submission_start'] = now();
            $data['submission_end'] = $dueDate->endOfDay();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send SMS notifications if enabled
        if ($this->record->notify_parents) {
            HomeworkResource::sendSmsNotifications($this->record);

            Notification::make()
                ->title('Homework Created Successfully')
                ->body('SMS notifications are being sent to parents')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Homework Created Successfully')
                ->body('No SMS notifications were sent')
                ->success()
                ->send();
        }
    }
}
