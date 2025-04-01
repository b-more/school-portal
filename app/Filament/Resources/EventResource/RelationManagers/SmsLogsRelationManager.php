<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Services\SmsService;
use Filament\Notifications\Notification;

class SmsLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'smsLogs';

    protected static ?string $recordTitleAttribute = 'recipient';

    public function form(Form $form): Form
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
                Forms\Components\TextInput::make('cost')
                    ->numeric()
                    ->prefix('ZMW')
                    ->step(0.01),
                Forms\Components\Textarea::make('error_message')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
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
                    ]),
                Tables\Columns\TextColumn::make('cost')
                    ->money('ZMW'),
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Sent By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['message_type'] = 'event_notification';
                        $data['reference_id'] = $this->ownerRecord->id;
                        $data['sent_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function ($record): void {
                        try {
                            // Resend the SMS
                            SmsService::send($record->message, $record->recipient);

                            // Create a new log
                            $this->ownerRecord->smsLogs()->create([
                                'recipient' => $record->recipient,
                                'message' => $record->message,
                                'status' => 'sent',
                                'message_type' => 'event_notification',
                                'reference_id' => $this->ownerRecord->id,
                                'cost' => $record->cost,
                                'sent_by' => auth()->id(),
                            ]);

                            // Show success notification
                            Notification::make()
                                ->title('SMS resent successfully')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            // Create a failed log
                            $this->ownerRecord->smsLogs()->create([
                                'recipient' => $record->recipient,
                                'message' => $record->message,
                                'status' => 'failed',
                                'message_type' => 'event_notification',
                                'reference_id' => $this->ownerRecord->id,
                                'error_message' => $e->getMessage(),
                                'sent_by' => auth()->id(),
                            ]);

                            // Show error notification
                            Notification::make()
                                ->title('Failed to resend SMS')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
