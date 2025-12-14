<?php

namespace App\Filament\Resources\StaffDesignationResource\Pages;

use App\Filament\Resources\StaffDesignationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaffDesignation extends CreateRecord
{
    protected static string $resource = StaffDesignationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
