<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\BusFareStructureResource\Pages;
use App\Models\BusFareStructure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusFareStructureResource extends Resource
{
    protected static ?string $model = BusFareStructure::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Finance Management';

    protected static ?string $navigationLabel = 'Bus Fare Configuration';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Route Information')
                    ->description('Configure bus route and payment details')
                    ->schema([
                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->required()
                            ->placeholder('e.g., City Center, Woodlands, Chelstone')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('payment_plan')
                            ->label('Payment Plan')
                            ->options([
                                'monthly' => 'Monthly',
                                'per_term' => 'Per Term',
                            ])
                            ->required()
                            ->default('per_term')
                            ->native(false)
                            ->live()
                            ->helperText('Choose whether students pay monthly or per term'),

                        Forms\Components\TextInput::make('monthly_amount')
                            ->label('Monthly Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required(fn (Get $get) => $get('payment_plan') === 'monthly')
                            ->visible(fn (Get $get) => $get('payment_plan') === 'monthly')
                            ->helperText('Amount to be paid monthly'),

                        Forms\Components\TextInput::make('term_amount')
                            ->label('Term Amount')
                            ->numeric()
                            ->prefix('ZMW')
                            ->required(fn (Get $get) => $get('payment_plan') === 'per_term')
                            ->visible(fn (Get $get) => $get('payment_plan') === 'per_term')
                            ->helperText('Amount to be paid per term'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable or disable this bus route'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional information about this route...'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('route_name')
                    ->label('Route')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-map-pin')
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('payment_plan')
                    ->label('Payment Plan')
                    ->colors([
                        'success' => 'per_term',
                        'info' => 'monthly',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'monthly' ? 'Monthly' : 'Per Term'),

                Tables\Columns\TextColumn::make('monthly_amount')
                    ->label('Monthly Fee')
                    ->money('ZMW')
                    ->sortable()
                    ->visible(fn ($record) => $record && $record->payment_plan === 'monthly')
                    ->color('success'),

                Tables\Columns\TextColumn::make('term_amount')
                    ->label('Term Fee')
                    ->money('ZMW')
                    ->sortable()
                    ->visible(fn ($record) => $record && $record->payment_plan === 'per_term')
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('busPayments_count')
                    ->label('Active Students')
                    ->counts('busPayments')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_plan')
                    ->options([
                        'monthly' => 'Monthly',
                        'per_term' => 'Per Term',
                    ])
                    ->native(false),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All routes')
                    ->trueLabel('Active routes')
                    ->falseLabel('Inactive routes'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('No Bus Routes Configured')
            ->emptyStateDescription('Create your first bus route and configure the payment plan.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusFareStructures::route('/'),
            'create' => Pages\CreateBusFareStructure::route('/create'),
            'view' => Pages\ViewBusFareStructure::route('/{record}'),
            'edit' => Pages\EditBusFareStructure::route('/{record}/edit'),
        ];
    }
}
