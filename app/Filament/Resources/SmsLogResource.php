<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Models\SmsLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('recipient')
                    ->tel()
                    ->required(),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ])
                    ->required(),
                Forms\Components\Select::make('message_type')
                    ->options([
                        'homework_notification' => 'Homework Notification',
                        'result_notification' => 'Result Notification',
                        'fee_reminder' => 'Fee Reminder',
                        'event_notification' => 'Event Notification',
                        'general' => 'General Message',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference_id')
                    ->numeric()
                    ->helperText('ID of the related record (homework, result, etc.)'),
                Forms\Components\TextInput::make('cost')
                    ->numeric()
                    ->prefix('ZMW')
                    ->step(0.01),
                Forms\Components\TextInput::make('provider_reference')
                    ->maxLength(255)
                    ->helperText('Message ID or reference from the SMS provider'),
                Forms\Components\Textarea::make('error_message')
                    ->columnSpanFull(),
                Forms\Components\Select::make('sent_by')
                    ->relationship('sender', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'delivered',
                        'info' => 'sent',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Sent By')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ]),
                Tables\Filters\SelectFilter::make('message_type')
                    ->options([
                        'homework_notification' => 'Homework Notification',
                        'result_notification' => 'Result Notification',
                        'fee_reminder' => 'Fee Reminder',
                        'event_notification' => 'Event Notification',
                        'general' => 'General Message',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('sent_by')
                    ->relationship('sender', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSmsLogs::route('/'),
            'create' => Pages\CreateSmsLog::route('/create'),
            //'view' => Pages\ViewSmsLog::route('/{record}'),
            'edit' => Pages\EditSmsLog::route('/{record}/edit'),
        ];
    }

    // Add badge to show total cost
    public static function getWidgets(): array
    {
        return [
           // SmsLogResource\Widgets\SmsCostOverview::class,
        ];
    }
}
