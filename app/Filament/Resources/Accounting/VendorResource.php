<?php

namespace App\Filament\Resources\Accounting;

use App\Constants\RoleConstants;
use App\Filament\Resources\Accounting\VendorResource\Pages;
use App\Models\Accounting\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Vendors/Suppliers';
    protected static ?int $navigationSort = 6;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Vendor')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('code')
                                    ->maxLength(20)
                                    ->placeholder('Auto-generated if empty'),

                                Forms\Components\TextInput::make('contact_person')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\TextInput::make('alternate_phone')
                                    ->tel()
                                    ->maxLength(20),

                                Forms\Components\Textarea::make('address')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('city')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('tax_pin')
                                    ->label('Tax PIN/TPIN')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('payment_terms')
                                    ->placeholder('e.g., Net 30')
                                    ->maxLength(100),

                                Forms\Components\Toggle::make('is_active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Bank Details')
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('bank_account_name')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('bank_account_number')
                                    ->maxLength(50),

                                Forms\Components\TextInput::make('bank_branch')
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Notes')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('contact_person')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('city')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('expenses_count')
                    ->label('Transactions')
                    ->counts('expenses')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
