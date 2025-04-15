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
            Actions\DeleteAction::make(),
        ];
    }
}
