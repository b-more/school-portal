<?php

namespace App\Filament\Resources\TeacherHomeworkResource\Pages;

use App\Filament\Resources\TeacherHomeworkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherHomework extends EditRecord
{
    protected static string $resource = TeacherHomeworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn() => !$this->record->submissions()->exists())
                ->requiresConfirmation()
                ->modalHeading('Delete Homework?')
                ->modalDescription('This will permanently delete the homework assignment.'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.teacher-homework.index');
    }
}
