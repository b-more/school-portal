<?php

namespace App\Filament\Resources\Accounting\BankTransactionResource\Pages;

use App\Filament\Resources\Accounting\BankTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankTransaction extends EditRecord
{
    protected static string $resource = BankTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
