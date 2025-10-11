<?php

namespace App\Filament\Resources\PaymentTransactionResource\Pages;

use App\Filament\Resources\PaymentTransactionResource;
use App\Filament\Resources\PaymentTransactionResource\Widgets\TodayPaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\WeekPaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\MonthPaymentsWidget;
use App\Filament\Resources\PaymentTransactionResource\Widgets\TermPaymentsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentTransactions extends ListRecords
{
    protected static string $resource = PaymentTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - payments are created through StudentFeeResource
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TodayPaymentsWidget::class,
            WeekPaymentsWidget::class,
            MonthPaymentsWidget::class,
            TermPaymentsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3; // Display widgets in 3 columns
    }
}
