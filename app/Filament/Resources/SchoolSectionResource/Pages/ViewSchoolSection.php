<?php

namespace App\Filament\Resources\SchoolSectionResource\Pages;

use App\Filament\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSchoolSection extends ViewRecord
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
