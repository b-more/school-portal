<?php

namespace App\Filament\Resources\GetInTouchResource\Pages;

use App\Filament\Resources\GetInTouchResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGetInTouch extends ViewRecord
{
    protected static string $resource = GetInTouchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
