<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
        ];
    }
}
