<?php

namespace App\Filament\Resources\SchoolSettingsResource\Pages;

use App\Filament\Resources\SchoolSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSettings extends ListRecords
{
    protected static string $resource = SchoolSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
