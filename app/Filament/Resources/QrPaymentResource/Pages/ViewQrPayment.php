<?php

namespace App\Filament\Resources\QrPaymentResource\Pages;

use App\Filament\Resources\QrPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQrPayment extends ViewRecord
{
    protected static string $resource = QrPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
