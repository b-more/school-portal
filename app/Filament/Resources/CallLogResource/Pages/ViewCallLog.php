<?php

namespace App\Filament\Resources\CallLogResource\Pages;

use App\Filament\Resources\CallLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCallLog extends ViewRecord
{
    protected static string $resource = CallLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
