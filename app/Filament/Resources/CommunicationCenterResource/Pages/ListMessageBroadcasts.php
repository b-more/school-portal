<?php

namespace App\Filament\Resources\CommunicationCenterResource\Pages;

use App\Filament\Resources\CommunicationCenterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMessageBroadcasts extends ListRecords
{
    protected static string $resource = CommunicationCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(fn () => CommunicationCenterResource::getUrl('create')),
        ];
    }
}
