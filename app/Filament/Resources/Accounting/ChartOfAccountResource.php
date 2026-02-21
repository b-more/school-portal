<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\ChartOfAccountResource\Pages;
use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\ChartOfAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Chart of Accounts';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\Select::make('account_category_id')
                            ->label('Category')
                            ->options(AccountCategory::active()->orderBy('sort_order')->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $category = AccountCategory::find($state);
                                    $set('account_type', $category->normal_balance);
                                }
                            }),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Account')
                            ->options(fn () => ChartOfAccount::active()
                                ->orderBy('code')
                                ->get()
                                ->pluck('full_name', 'id'))
                            ->searchable()
                            ->placeholder('None (Top Level)'),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., 1001'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('account_type')
                            ->options([
                                'debit' => 'Debit',
                                'credit' => 'Credit',
                            ])
                            ->required()
                            ->default('debit'),

                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('allow_direct_posting')
                            ->label('Allow Direct Posting')
                            ->helperText('If disabled, this account can only be used as a parent account')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => match ($record->category?->type) {
                        'asset' => 'info',
                        'liability' => 'warning',
                        'equity' => 'success',
                        'revenue' => 'success',
                        'expense' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->color(fn ($state) => $state === 'debit' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('current_balance')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('allow_direct_posting')
                    ->label('Postable')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_category_id')
                    ->label('Category')
                    ->options(AccountCategory::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('category_type')
                    ->label('Type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'revenue' => 'Revenue',
                        'expense' => 'Expense',
                    ])
                    ->query(fn ($query, $data) => $data['value']
                        ? $query->whereHas('category', fn($q) => $q->where('type', $data['value']))
                        : $query),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChartOfAccounts::route('/'),
            'create' => Pages\CreateChartOfAccount::route('/create'),
            'edit' => Pages\EditChartOfAccount::route('/{record}/edit'),
        ];
    }
}
