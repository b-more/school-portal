<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\ExpenseCategoryResource\Pages;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\ExpenseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExpenseCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Expense Categories';
    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Category')
                            ->options(ExpenseCategory::active()->whereNull('parent_id')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('None (Top Level)'),

                        Forms\Components\Select::make('account_id')
                            ->label('Linked Account')
                            ->options(ChartOfAccount::active()
                                ->expenses()
                                ->orderBy('code')
                                ->get()
                                ->pluck('full_name', 'id'))
                            ->searchable()
                            ->helperText('The expense account to debit when recording expenses'),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('budget_amount')
                            ->label('Annual Budget')
                            ->numeric()
                            ->prefix('ZMW')
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
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

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('account.full_name')
                    ->label('Linked Account')
                    ->limit(30)
                    ->placeholder('Not linked'),

                Tables\Columns\TextColumn::make('budget_amount')
                    ->label('Budget')
                    ->money('ZMW')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('expenses_count')
                    ->label('Expenses')
                    ->counts('expenses')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(ExpenseCategory::whereNull('parent_id')->pluck('name', 'id'))
                    ->placeholder('All'),

                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenseCategories::route('/'),
            'create' => Pages\CreateExpenseCategory::route('/create'),
            'edit' => Pages\EditExpenseCategory::route('/{record}/edit'),
        ];
    }
}
