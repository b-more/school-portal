<?php

namespace App\Filament\Resources\Accounting\PaymentVoucherResource\Pages;

use App\Filament\Resources\Accounting\PaymentVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentVoucher extends EditRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->isPending()),
        ];
    }
}
