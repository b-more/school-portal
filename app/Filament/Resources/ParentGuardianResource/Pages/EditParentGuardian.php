<?php

namespace App\Filament\Resources\ParentGuardianResource\Pages;

use App\Filament\Resources\ParentGuardianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditParentGuardian extends EditRecord
{
    protected static string $resource = ParentGuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
