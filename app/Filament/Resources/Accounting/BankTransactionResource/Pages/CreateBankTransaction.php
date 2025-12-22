<?php

namespace App\Filament\Resources\Accounting\BankTransactionResource\Pages;

use App\Filament\Resources\Accounting\BankTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankTransaction extends CreateRecord
{
    protected static string $resource = BankTransactionResource::class;
}
