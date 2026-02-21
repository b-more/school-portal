<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPayroll extends ViewRecord
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print_payslip')
                ->label('Print Payslip')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('payslips.stream', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('download_payslip')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('payslips.download', $this->record)),

            Actions\EditAction::make(),
        ];
    }
}
