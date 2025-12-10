<?php

namespace App\Filament\Resources\GradeSubjectResource\Pages;

use App\Filament\Resources\GradeSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGradeSubjects extends ListRecords
{
    protected static string $resource = GradeSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
