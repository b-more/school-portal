<?php

namespace App\Filament\Resources\QrPaymentResource\Pages;

use App\Filament\Resources\QrPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQrPayments extends ListRecords
{
    protected static string $resource = QrPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('print_standard_qr')
                ->label('Print Standard QR Code')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->modalHeading('Standard QR Code for Payment')
                ->modalDescription('Print this QR code and distribute to parents. They can scan it to access the payment portal.')
                ->modalContent(fn () => view('filament.modals.standard-qr-code'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalWidth('md'),
        ];
    }
}
