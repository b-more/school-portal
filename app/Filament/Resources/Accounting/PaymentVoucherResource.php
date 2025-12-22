<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\PaymentVoucherResource\Pages;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\PaymentVoucher;
use App\Models\Accounting\Vendor;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PaymentVoucherResource extends Resource
{
    protected static ?string $model = PaymentVoucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Payment Vouchers';
    protected static ?int $navigationSort = 8;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Voucher Details')
                    ->schema([
                        Forms\Components\TextInput::make('voucher_number')
                            ->default(fn () => PaymentVoucher::generateVoucherNumber())
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\DatePicker::make('voucher_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('payee_type')
                            ->options([
                                'vendor' => 'Vendor/Supplier',
                                'employee' => 'Employee',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('payee_id', null)),

                        Forms\Components\Select::make('payee_id')
                            ->label('Payee')
                            ->options(function (Forms\Get $get) {
                                return match ($get('payee_type')) {
                                    'vendor' => Vendor::active()->pluck('name', 'id'),
                                    'employee' => Employee::where('status', 'active')->pluck('name', 'id'),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => in_array($get('payee_type'), ['vendor', 'employee']))
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                if ($state && $get('payee_type') === 'vendor') {
                                    $vendor = Vendor::find($state);
                                    $set('payee_name', $vendor?->name);
                                } elseif ($state && $get('payee_type') === 'employee') {
                                    $employee = Employee::find($state);
                                    $set('payee_name', $employee?->name);
                                }
                            }),

                        Forms\Components\TextInput::make('payee_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                $set('amount_in_words', PaymentVoucher::numberToWords($state ?? 0))),

                        Forms\Components\TextInput::make('amount_in_words')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options(PaymentVoucher::PAYMENT_METHODS)
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(BankAccount::active()->get()->mapWithKeys(fn ($a) => [$a->id => $a->display_name]))
                            ->visible(fn (Forms\Get $get) => $get('payment_method') !== 'cash'),

                        Forms\Components\TextInput::make('cheque_number')
                            ->visible(fn (Forms\Get $get) => $get('payment_method') === 'cheque'),

                        Forms\Components\Textarea::make('notes')
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
                Tables\Columns\TextColumn::make('voucher_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('voucher_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payee_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payee_type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'vendor' => 'info',
                        'employee' => 'success',
                        'other' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30),

                Tables\Columns\TextColumn::make('amount')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'info',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('payee_type')
                    ->options([
                        'vendor' => 'Vendor',
                        'employee' => 'Employee',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(PaymentVoucher::PAYMENT_METHODS),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn ($record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->approve(auth()->id());
                        Notification::make()->title('Voucher approved')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Pay')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn ($record) => $record->isApproved())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsPaid(auth()->id());
                        Notification::make()->title('Voucher marked as paid')->success()->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->isPending()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => false), // Disable bulk delete
                ]),
            ])
            ->defaultSort('voucher_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentVouchers::route('/'),
            'create' => Pages\CreatePaymentVoucher::route('/create'),
            'edit' => Pages\EditPaymentVoucher::route('/{record}/edit'),
        ];
    }
}
