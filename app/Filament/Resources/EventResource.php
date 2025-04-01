<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\Student;
use App\Models\SmsLog;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Website Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('category')
                            ->options([
                                'academic' => 'Academic',
                                'sports' => 'Sports',
                                'cultural' => 'Cultural',
                                'religious' => 'Religious',
                                'other' => 'Other',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Date & Location')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->required(),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('event-images')
                            ->maxSize(2048),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Event Management')
                    ->schema([
                        Forms\Components\Select::make('organizer_id')
                            ->relationship('organizer', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('upcoming'),
                    ])->columns(2),

                Forms\Components\Section::make('Notification Settings')
                    ->schema([
                        Forms\Components\Toggle::make('notify_parents')
                            ->label('Send SMS notifications to parents')
                            ->default(false)
                            ->helperText('This will send SMS notifications to all parents/guardians'),
                        Forms\Components\Select::make('target_grades')
                            ->label('Target Grades (leave empty for all)')
                            ->multiple()
                            ->options(function () {
                                return Student::select('grade')
                                    ->distinct()
                                    ->pluck('grade', 'grade')
                                    ->toArray();
                            })
                            ->visible(fn (callable $get) => $get('notify_parents'))
                            ->helperText('Select specific grades to target, or leave empty to notify all parents'),
                        Forms\Components\Textarea::make('sms_message')
                            ->label('Custom SMS Message (optional)')
                            ->placeholder('Leave empty to use the default message template')
                            ->helperText('Default template includes event title, date, time, and location')
                            ->visible(fn (callable $get) => $get('notify_parents'))
                            ->maxLength(160),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('organizer.name')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'upcoming',
                        'success' => 'ongoing',
                        'info' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('sms_notifications')
                    ->getStateUsing(fn ($record) => $record->smsLogs()->count())
                    ->label('SMS Sent')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'academic' => 'Academic',
                        'sports' => 'Sports',
                        'cultural' => 'Cultural',
                        'religious' => 'Religious',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('organizer')
                    ->relationship('organizer', 'name'),
                Tables\Filters\Filter::make('start_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('sendNotifications')
                    ->label('Send SMS Notifications')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Event $record) {
                        self::sendEventNotifications($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send Event Notifications')
                    ->modalDescription('This will send SMS notifications to parents/guardians. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notifications'),
                Tables\Actions\Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'Upcoming',
                                'ongoing' => 'Ongoing',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update(['status' => $data['status']]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'upcoming' => 'Upcoming',
                                    'ongoing' => 'Ongoing',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                        ])
                        ->action(function (Builder $query, array $data): void {
                            $query->update(['status' => $data['status']]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('sendBulkNotifications')
                        ->label('Send SMS Notifications')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->action(function (Builder $query) {
                            $events = $query->where('status', 'upcoming')->get();

                            $successCount = 0;
                            $failedCount = 0;

                            foreach ($events as $event) {
                                try {
                                    $result = self::sendEventNotifications($event, false);
                                    $successCount += $result['success'];
                                    $failedCount += $result['failed'];
                                } catch (\Exception $e) {
                                    $failedCount++;
                                    Log::error('Failed to send event notifications', [
                                        'event_id' => $event->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title("Event Notifications: {$successCount} sent successfully, {$failedCount} failed")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Bulk Event Notifications')
                        ->modalDescription('This will send SMS notifications for all selected upcoming events. Are you sure you want to continue?')
                        ->modalSubmitActionLabel('Yes, Send All Notifications'),
                ]),
            ]);
    }

    protected static function sendEventNotifications(Event $event, $showNotification = true)
    {
        // Determine which students to target
        $query = Student::where('enrollment_status', 'active')->with('parentGuardian');

        // Filter by grades if specified
        if (!empty($event->target_grades)) {
            $query->whereIn('grade', $event->target_grades);
        }

        $students = $query->get();

        $successCount = 0;
        $failedCount = 0;

        $startDate = $event->start_date->format('d/m/Y');
        $startTime = $event->start_date->format('h:i A');

        foreach ($students as $student) {
            if (!$student->parentGuardian || !$student->parentGuardian->phone) {
                continue;
            }

            // Format message
            $customMessage = $event->sms_message;

            if (empty($customMessage)) {
                $message = "St. Francis of Assisi School Event: \"{$event->title}\" on {$startDate} at {$startTime}, {$event->location}. ";

                // Add status information if relevant
                if ($event->status === 'cancelled') {
                    $message .= "NOTE: This event has been CANCELLED. ";
                }

                $message .= "For details visit: https://stfrancisofassisi.site";

                // Ensure message fits within SMS limits
                if (strlen($message) > 160) {
                    $message = substr($message, 0, 157) . "...";
                }
            } else {
                $message = $customMessage;
            }

            try {
                // Send SMS
                SmsService::send($message, $student->parentGuardian->phone);

                // Log the SMS
                SmsLog::create([
                    'recipient' => $student->parentGuardian->phone,
                    'message' => $message,
                    'status' => 'sent',
                    'message_type' => 'event_notification',
                    'reference_id' => $event->id,
                    'cost' => 1, // Assuming cost per SMS, adjust as needed
                    'sent_by' => auth()->id(),
                ]);

                $successCount++;
            } catch (\Exception $e) {
                // Log the error
                Log::error('Failed to send event notification SMS', [
                    'student_id' => $student->id,
                    'parent_phone' => $student->parentGuardian->phone,
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ]);

                // Log the failed SMS
                SmsLog::create([
                    'recipient' => $student->parentGuardian->phone,
                    'message' => $message,
                    'status' => 'failed',
                    'message_type' => 'event_notification',
                    'reference_id' => $event->id,
                    'error_message' => $e->getMessage(),
                    'sent_by' => auth()->id(),
                ]);

                $failedCount++;
            }
        }

        // Display notification with results if requested
        if ($showNotification) {
            if ($successCount > 0 || $failedCount > 0) {
                $message = "SMS Notifications: {$successCount} sent successfully";
                if ($failedCount > 0) {
                    $message .= ", {$failedCount} failed";
                }

                Notification::make()
                    ->title($message)
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('No eligible parents found')
                    ->body('No SMS notifications were sent because no eligible parents were found.')
                    ->warning()
                    ->send();
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failedCount,
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SmsLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
