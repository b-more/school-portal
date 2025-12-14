<?php

namespace App\Filament\Resources\StaffDesignationResource\Pages;

use App\Filament\Resources\StaffDesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffDesignation extends EditRecord
{
    protected static string $resource = StaffDesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
