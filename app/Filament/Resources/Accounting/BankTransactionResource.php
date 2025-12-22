<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\BankTransactionResource\Pages;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\BankTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class BankTransactionResource extends Resource
{
    protected static ?string $model = BankTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Bank Transactions';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Transaction Details')
                    ->schema([
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(BankAccount::active()->get()->mapWithKeys(fn ($a) => [$a->id => $a->display_name]))
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('type')
                            ->options(BankTransaction::TYPES)
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW'),

                        Forms\Components\TextInput::make('reference')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('payee')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('bankAccount.display_name')
                    ->label('Bank Account')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'deposit', 'interest' => 'success',
                        'withdrawal', 'transfer' => 'warning',
                        'fee', 'charge' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('payee')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reference')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($record) => $record->isDeposit() ? 'success' : 'danger')
                    ->formatStateUsing(fn ($record) => ($record->isDeposit() ? '+' : '-') . number_format($record->amount, 2)),

                Tables\Columns\IconColumn::make('reconciled')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bank_account_id')
                    ->label('Bank Account')
                    ->options(BankAccount::all()->mapWithKeys(fn ($a) => [$a->id => $a->display_name])),

                Tables\Filters\SelectFilter::make('type')
                    ->options(BankTransaction::TYPES),

                Tables\Filters\TernaryFilter::make('reconciled'),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('transaction_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('transaction_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('reconcile')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->reconciled)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->reconcile(auth()->id());
                        Notification::make()->title('Transaction reconciled')->success()->send();
                    }),

                Tables\Actions\Action::make('unreconcile')
                    ->icon('heroicon-o-x-mark')
                    ->color('warning')
                    ->visible(fn ($record) => $record->reconciled)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->unreconcile();
                        Notification::make()->title('Reconciliation removed')->success()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('reconcile_selected')
                        ->label('Reconcile Selected')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->reconcile(auth()->id()));
                            Notification::make()->title('Transactions reconciled')->success()->send();
                        }),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankTransactions::route('/'),
            'create' => Pages\CreateBankTransaction::route('/create'),
            'edit' => Pages\EditBankTransaction::route('/{record}/edit'),
        ];
    }
}
