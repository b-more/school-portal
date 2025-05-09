<?php

namespace App\Filament\Resources\SchoolSectionResource\Pages;

use App\Filament\Resources\SchoolSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolSection extends EditRecord
{
    protected static string $resource = SchoolSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
