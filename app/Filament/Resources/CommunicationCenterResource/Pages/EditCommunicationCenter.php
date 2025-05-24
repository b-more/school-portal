<?php

namespace App\Filament\Resources\CommunicationCenterResource\Pages;

use App\Filament\Resources\CommunicationCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommunicationCenter extends EditRecord
{
    protected static string $resource = CommunicationCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
