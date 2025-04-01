<?php

namespace App\Filament\Resources\GetInTouchResource\Pages;

use App\Filament\Resources\GetInTouchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGetInTouches extends ListRecords
{
    protected static string $resource = GetInTouchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
