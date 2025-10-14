<?php

namespace App\Filament\Resources\BusFareStructureResource\Pages;

use App\Filament\Resources\BusFareStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusFareStructures extends ListRecords
{
    protected static string $resource = BusFareStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
