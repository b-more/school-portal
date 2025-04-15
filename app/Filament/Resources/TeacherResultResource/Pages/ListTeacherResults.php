<?php

namespace App\Filament\Resources\TeacherResultResource\Pages;

use App\Filament\Resources\TeacherResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherResults extends ListRecords
{
    protected static string $resource = TeacherResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
