<?php

namespace App\Filament\Resources\TeacherHomeworkSubmissionResource\Pages;

use App\Filament\Resources\TeacherHomeworkSubmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherHomeworkSubmissions extends ListRecords
{
    protected static string $resource = TeacherHomeworkSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
