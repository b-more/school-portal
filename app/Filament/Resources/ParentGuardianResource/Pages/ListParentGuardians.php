<?php

namespace App\Filament\Resources\ParentGuardianResource\Pages;

use App\Filament\Resources\ParentGuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParentGuardians extends ListRecords
{
    protected static string $resource = ParentGuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
