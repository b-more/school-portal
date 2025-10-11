<?php

namespace App\Filament\Resources\QrPaymentResource\Pages;

use App\Filament\Resources\QrPaymentResource;
use App\Models\QrPayment;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQrPayment extends CreateRecord
{
    protected static string $resource = QrPaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate QR code
        $data['qr_code'] = QrPayment::generateQrCode(
            $data['payment_reference'],
            $data['amount'],
            $data['customer_mobile']
        );

        // Set initiated timestamp
        $data['initiated_at'] = now();

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
