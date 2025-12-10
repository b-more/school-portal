<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Constants\RoleConstants;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        // Only show create button for admins
        if (auth()->user()?->role_id !== RoleConstants::ADMIN) {
            return [];
        }

        return [
            Actions\CreateAction::make(),
        ];
    }
}
