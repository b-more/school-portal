<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Constants\RoleConstants;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }
}
