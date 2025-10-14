<?php

namespace App\Filament\Resources\BusFareStructureResource\Pages;

use App\Filament\Resources\BusFareStructureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusFareStructure extends EditRecord
{
    protected static string $resource = BusFareStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
