<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\BankAccountResource\Pages;
use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Bank Accounts';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bank Information')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('account_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('account_number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('branch')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('swift_code')
                            ->label('SWIFT/BIC Code')
                            ->maxLength(20),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'ZMW' => 'ZMW - Zambian Kwacha',
                                'USD' => 'USD - US Dollar',
                            ])
                            ->default('ZMW')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Settings')
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->label('Linked GL Account')
                            ->options(ChartOfAccount::active()
                                ->assets()
                                ->orderBy('code')
                                ->get()
                                ->pluck('full_name', 'id'))
                            ->searchable()
                            ->helperText('Link to a Chart of Account for journal entries'),

                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),

                        Forms\Components\TextInput::make('current_balance')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Account')
                            ->helperText('Use this as the default bank account')
                            ->inline(false),

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
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->formatStateUsing(fn ($record) => $record->masked_account_number),

                Tables\Columns\TextColumn::make('branch')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('currency')
                    ->badge(),

                Tables\Columns\TextColumn::make('current_balance')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_default'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_transactions')
                    ->label('Transactions')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => BankTransactionResource::getUrl('index', ['tableFilters[bank_account_id][value]' => $record->id])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('bank_name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}
