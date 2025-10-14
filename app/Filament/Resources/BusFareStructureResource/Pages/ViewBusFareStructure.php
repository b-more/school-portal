<?php

namespace App\Filament\Resources\BusFareStructureResource\Pages;

use App\Filament\Resources\BusFareStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusFareStructure extends ViewRecord
{
    protected static string $resource = BusFareStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
