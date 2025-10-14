<?php

namespace App\Filament\Resources\BusPaymentResource\Pages;

use App\Filament\Resources\BusPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBusPayments extends ListRecords
{
    protected static string $resource = BusPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
