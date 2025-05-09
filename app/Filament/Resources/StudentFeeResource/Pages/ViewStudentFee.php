<?php

namespace App\Filament\Resources\StudentFeeResource\Pages;

use App\Filament\Resources\StudentFeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentFee extends ViewRecord
{
    protected static string $resource = StudentFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('printReceipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('student-fees.receipt', $this->record))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),
            Actions\Action::make('sendPaymentSMS')
                ->label('Send SMS Receipt')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('warning')
                ->action(function () {
                    StudentFeeResource::sendPaymentSMS($this->record);
                })
                ->visible(fn () => $this->record->payment_status !== 'unpaid'),
        ];
    }
}
