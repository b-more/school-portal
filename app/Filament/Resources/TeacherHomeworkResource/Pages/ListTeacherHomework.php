<?php

namespace App\Filament\Resources\TeacherHomeworkResource\Pages;

use App\Filament\Resources\TeacherHomeworkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherHomework extends ListRecords
{
    protected static string $resource = TeacherHomeworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
