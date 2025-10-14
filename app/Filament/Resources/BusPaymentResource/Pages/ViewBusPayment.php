<?php

namespace App\Filament\Resources\BusPaymentResource\Pages;

use App\Filament\Resources\BusPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBusPayment extends ViewRecord
{
    protected static string $resource = BusPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
