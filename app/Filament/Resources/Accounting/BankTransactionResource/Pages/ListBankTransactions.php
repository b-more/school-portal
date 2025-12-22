<?php

namespace App\Filament\Resources\Accounting\BankTransactionResource\Pages;

use App\Filament\Resources\Accounting\BankTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankTransactions extends ListRecords
{
    protected static string $resource = BankTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
