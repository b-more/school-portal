<?php

namespace App\Filament\Resources\StaffDesignationResource\Pages;

use App\Filament\Resources\StaffDesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffDesignations extends ListRecords
{
    protected static string $resource = StaffDesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
