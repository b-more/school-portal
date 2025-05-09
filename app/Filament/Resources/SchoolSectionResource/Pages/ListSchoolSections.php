<?php

namespace App\Filament\Resources\SchoolSectionResource\Pages;

use App\Filament\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSections extends ListRecords
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
