<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\SmsCreditResource\Pages;
use App\Models\SmsCredit;
use App\Models\SmsCreditTransaction;
use App\Services\SmsCreditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsCreditResource extends Resource
{
    protected static ?string $model = SmsCreditTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'SMS Credits';

    protected static ?string $modelLabel = 'SMS Credit';

    protected static ?string $pluralModelLabel = 'SMS Credits';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $credit = SmsCredit::first();
        if ($credit && $credit->isBalanceLow()) {
            return 'Low';
        }
        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $credit = SmsCredit::first();
        if ($credit && $credit->isBalanceLow()) {
            return 'danger';
        }
        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // This resource is primarily for viewing transactions
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        'adjustment' => 'warning',
                        'refund' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'credit' => 'Top Up',
                        'debit' => 'SMS Sent',
                        'adjustment' => 'Adjustment',
                        'refund' => 'Refund',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Credits')
                    ->color(fn (SmsCreditTransaction $record): string =>
                        in_array($record->type, ['credit', 'refund']) ? 'success' : 'danger'
                    )
                    ->formatStateUsing(fn (SmsCreditTransaction $record): string =>
                        (in_array($record->type, ['credit', 'refund']) ? '+' : '-') .
                        number_format($record->amount) . ' credits'
                    ),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Balance')
                    ->formatStateUsing(fn ($state): string => number_format($state) . ' credits')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(fn (SmsCreditTransaction $record): string => $record->description),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('By')
                    ->placeholder('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'credit' => 'Top Up',
                        'debit' => 'SMS Sent',
                        'adjustment' => 'Adjustment',
                        'refund' => 'Refund',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading('Transaction Details')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('type_display')
                                    ->label('Transaction Type')
                                    ->content(fn (SmsCreditTransaction $record): string => $record->type_label),

                                Forms\Components\Placeholder::make('amount_display')
                                    ->label('Credits')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        number_format($record->amount) . ' credits'),

                                Forms\Components\Placeholder::make('balance_before_display')
                                    ->label('Balance Before')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        number_format($record->balance_before) . ' credits'),

                                Forms\Components\Placeholder::make('balance_after_display')
                                    ->label('Balance After')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        number_format($record->balance_after) . ' credits'),

                                Forms\Components\Placeholder::make('description_display')
                                    ->label('Description')
                                    ->content(fn (SmsCreditTransaction $record): string => $record->description)
                                    ->columnSpanFull(),

                                Forms\Components\Placeholder::make('reference_display')
                                    ->label('Reference')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        $record->reference ?? 'N/A'),

                                Forms\Components\Placeholder::make('performed_by_display')
                                    ->label('Performed By')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        $record->performedBy?->name ?? 'System'),

                                Forms\Components\Placeholder::make('created_at_display')
                                    ->label('Date & Time')
                                    ->content(fn (SmsCreditTransaction $record): string =>
                                        $record->created_at->format('M d, Y H:i:s'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->bulkActions([])
            ->headerActions([
                Tables\Actions\Action::make('topUp')
                    ->label('Top Up Credits')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Number of Credits')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->integer()
                            ->suffix('credits')
                            ->helperText(fn () => 'Current balance: ' .
                                number_format(SmsCredit::first()?->balance ?? 0) . ' credits'),

                        Forms\Components\TextInput::make('reference')
                            ->label('Reference / Receipt Number')
                            ->placeholder('e.g., INV-2025-001')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->default('SMS credit top up')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (array $data) {
                        $creditService = new SmsCreditService();
                        $creditService->addCredit(
                            (int) $data['amount'],
                            $data['description'],
                            $data['reference'] ?? null
                        );

                        Notification::make()
                            ->title('Credits Added')
                            ->body(number_format($data['amount']) . ' credits have been added.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Top Up SMS Credits')
                    ->modalSubmitActionLabel('Add Credits'),

                Tables\Actions\Action::make('settings')
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('gray')
                    ->form([
                        Forms\Components\TextInput::make('cost_per_sms')
                            ->label('Credits per SMS Part')
                            ->numeric()
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->default(fn () => SmsCredit::first()?->cost_per_sms ?? 1)
                            ->helperText('Credits deducted for each 160-character SMS part'),

                        Forms\Components\TextInput::make('low_balance_threshold')
                            ->label('Low Balance Alert Threshold')
                            ->numeric()
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(fn () => SmsCredit::first()?->low_balance_threshold ?? 50)
                            ->suffix('credits')
                            ->helperText('Alert when balance falls below this number of credits'),

                        Forms\Components\Toggle::make('allow_negative_balance')
                            ->label('Allow Sending with Insufficient Balance')
                            ->default(fn () => SmsCredit::first()?->allow_negative_balance ?? false)
                            ->helperText('If enabled, SMS will be sent even when credits are insufficient'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Enable SMS Sending')
                            ->default(fn () => SmsCredit::first()?->is_active ?? true)
                            ->helperText('Disable to prevent all SMS sending'),
                    ])
                    ->action(function (array $data) {
                        $creditService = new SmsCreditService();
                        $creditService->updateSettings($data);

                        Notification::make()
                            ->title('Settings Updated')
                            ->body('SMS credit settings have been updated.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('SMS Credit Settings'),

                Tables\Actions\Action::make('adjust')
                    ->label('Adjust Balance')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Forms\Components\Placeholder::make('current_balance')
                            ->label('Current Balance')
                            ->content(fn () => number_format(SmsCredit::first()?->balance ?? 0) . ' credits'),

                        Forms\Components\TextInput::make('new_balance')
                            ->label('New Balance')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->integer()
                            ->suffix('credits'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Adjustment')
                            ->required()
                            ->rows(2)
                            ->placeholder('e.g., Correction for miscounted credits'),
                    ])
                    ->action(function (array $data) {
                        $creditService = new SmsCreditService();
                        $creditService->adjustBalance(
                            (int) $data['new_balance'],
                            $data['reason']
                        );

                        Notification::make()
                            ->title('Balance Adjusted')
                            ->body('SMS credit balance has been adjusted to ' .
                                number_format($data['new_balance']) . ' credits')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Adjust SMS Credit Balance')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to adjust the balance? This action will be logged.'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsCredits::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->role_id === RoleConstants::ADMIN;
    }
}
