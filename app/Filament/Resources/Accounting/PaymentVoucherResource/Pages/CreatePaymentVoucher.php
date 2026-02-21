<?php

namespace App\Filament\Resources\Accounting\PaymentVoucherResource\Pages;

use App\Filament\Resources\Accounting\PaymentVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentVoucher extends CreateRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prepared_by'] = auth()->id();
        return $data;
    }
}
