<?php

namespace App\Filament\Resources\GetInTouchResource\Pages;

use App\Filament\Resources\GetInTouchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGetInTouch extends EditRecord
{
    protected static string $resource = GetInTouchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
