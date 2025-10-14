<?php

namespace App\Filament\Resources\BusPaymentResource\Pages;

use App\Filament\Resources\BusPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusPayment extends EditRecord
{
    protected static string $resource = BusPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
