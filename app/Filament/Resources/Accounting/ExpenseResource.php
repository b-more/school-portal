<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\ExpenseResource\Pages;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Expense;
use App\Models\Accounting\ExpenseCategory;
use App\Models\Accounting\Vendor;
use App\Models\AcademicYear;
use App\Services\Accounting\AccountingIntegrationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?int $navigationSort = 7;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Expense Details')
                    ->schema([
                        Forms\Components\TextInput::make('expense_number')
                            ->default(fn () => Expense::generateExpenseNumber())
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\DatePicker::make('expense_date')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('expense_category_id')
                            ->label('Category')
                            ->options(ExpenseCategory::active()->orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('vendor_id')
                            ->label('Vendor/Supplier')
                            ->options(Vendor::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select or leave empty'),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                                $set('total_amount', ($state ?? 0) + ($get('tax_amount') ?? 0))),

                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set, Forms\Get $get) =>
                                $set('total_amount', ($get('amount') ?? 0) + ($state ?? 0))),

                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(AcademicYear::orderByDesc('name')->pluck('name', 'id'))
                            ->default(fn () => AcademicYear::current()?->id),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'partial' => 'Partial',
                                'paid' => 'Paid',
                            ])
                            ->default('unpaid')
                            ->live(),

                        Forms\Components\TextInput::make('amount_paid')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => in_array($get('payment_status'), ['partial', 'paid'])),

                        Forms\Components\Select::make('payment_method')
                            ->options(Expense::PAYMENT_METHODS)
                            ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),

                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(255)
                            ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),

                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(BankAccount::active()->get()->mapWithKeys(fn ($a) => [$a->id => $a->display_name]))
                            ->visible(fn (Forms\Get $get) => $get('payment_status') !== 'unpaid'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->multiple()
                            ->directory('expenses')
                            ->maxFiles(5)
                            ->acceptedFileTypes(['application/pdf', 'image/*']),

                        Forms\Components\Textarea::make('notes')
                            ->rows(2),
                    ])
                    ->columns(1)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('expense_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->searchable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->description),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('expense_category_id')
                    ->label('Category')
                    ->options(ExpenseCategory::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(Vendor::pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'partial' => 'Partial',
                        'paid' => 'Paid',
                    ]),

                Tables\Filters\Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('expense_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('expense_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment_status !== 'paid')
                    ->form([
                        Forms\Components\Select::make('payment_method')
                            ->options(Expense::PAYMENT_METHODS)
                            ->required(),
                        Forms\Components\TextInput::make('payment_reference'),
                        Forms\Components\Select::make('bank_account_id')
                            ->label('Bank Account')
                            ->options(BankAccount::active()->get()->mapWithKeys(fn ($a) => [$a->id => $a->display_name])),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'payment_status' => 'paid',
                            'amount_paid' => $record->total_amount,
                            'payment_method' => $data['payment_method'],
                            'payment_reference' => $data['payment_reference'],
                            'bank_account_id' => $data['bank_account_id'],
                        ]);

                        // Post to accounting
                        app(AccountingIntegrationService::class)->postExpense($record);

                        Notification::make()
                            ->title('Expense marked as paid')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
