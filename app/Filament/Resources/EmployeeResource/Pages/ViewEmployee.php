<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
        ];
    }
}
