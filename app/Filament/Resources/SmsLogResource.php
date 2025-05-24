<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Filament\Resources\SmsLogResource\Widgets;
use App\Models\SmsLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    // Disable resource creation
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recipient')
                    ->label('Phone Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('message')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'delivered',
                        'primary' => 'sent',
                        'danger' => 'failed',
                        'warning' => 'pending',
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message_type')
                    ->label('Type')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sender.name')
                    ->label('Sent By')
                    ->searchable()
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
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (SmsLog $record) => $record->status !== 'pending')
                    ->action(function (SmsLog $record) {
                        // Resend the SMS
                        try {
                            // Replace @ with (at) for SMS compatibility
                            $sms_message = str_replace('@', '(at)', $record->message);
                            $url_encoded_message = urlencode($sms_message);

                            // Send the SMS
                            $sendSenderSMS = Http::withoutVerifying()
                                ->timeout(20)
                                ->connectTimeout(10)
                                ->retry(3, 2000)
                                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $record->recipient . '&api_key=121231313213123123');

                            $isSuccessful = $sendSenderSMS->successful() &&
                                          (strtolower($sendSenderSMS->body()) === 'success' ||
                                           strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

                            // Create a new SMS log entry for the resent message
                            $newSmsLog = SmsLog::create([
                                'recipient' => $record->recipient,
                                'message' => $record->message,
                                'status' => $isSuccessful ? 'sent' : 'failed',
                                'message_type' => $record->message_type,
                                'reference_id' => $record->reference_id,
                                'cost' => $record->cost,
                                'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
                                'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
                                'sent_by' => Auth::id(),
                            ]);

                            Notification::make()
                                ->title($isSuccessful ? 'SMS Resent Successfully' : 'Failed to Resend SMS')
                                ->body($isSuccessful
                                    ? "Message was successfully resent to {$record->recipient}"
                                    : "Could not resend message to {$record->recipient}. Error: {$sendSenderSMS->body()}")
                                ->color($isSuccessful ? 'success' : 'danger')
                                ->send();

                        } catch (\Exception $e) {
                            // Log error and create a failed SMS log
                            SmsLog::create([
                                'recipient' => $record->recipient,
                                'message' => $record->message,
                                'status' => 'failed',
                                'message_type' => $record->message_type,
                                'reference_id' => $record->reference_id,
                                'cost' => $record->cost,
                                'error_message' => "Resend failed: {$e->getMessage()}",
                                'sent_by' => Auth::id(),
                            ]);

                            Notification::make()
                                ->title('Failed to Resend SMS')
                                ->body("An error occurred: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('resendBulk')
                        ->label('Resend Selected')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $successCount = 0;
                            $failCount = 0;

                            foreach ($records as $record) {
                                try {
                                    // Replace @ with (at) for SMS compatibility
                                    $sms_message = str_replace('@', '(at)', $record->message);
                                    $url_encoded_message = urlencode($sms_message);

                                    // Send the SMS
                                    $sendSenderSMS = Http::withoutVerifying()
                                        ->timeout(20)
                                        ->connectTimeout(10)
                                        ->retry(3, 2000)
                                        ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $record->recipient . '&api_key=121231313213123123');

                                    $isSuccessful = $sendSenderSMS->successful() &&
                                                  (strtolower($sendSenderSMS->body()) === 'success' ||
                                                   strpos(strtolower($sendSenderSMS->body()), 'success') !== false);

                                    // Create a new SMS log entry for the resent message
                                    SmsLog::create([
                                        'recipient' => $record->recipient,
                                        'message' => $record->message,
                                        'status' => $isSuccessful ? 'sent' : 'failed',
                                        'message_type' => $record->message_type,
                                        'reference_id' => $record->reference_id,
                                        'cost' => $record->cost,
                                        'provider_reference' => $sendSenderSMS->json('message_id') ?? null,
                                        'error_message' => $isSuccessful ? null : $sendSenderSMS->body(),
                                        'sent_by' => Auth::id(),
                                    ]);

                                    if ($isSuccessful) {
                                        $successCount++;
                                    } else {
                                        $failCount++;
                                    }

                                    // Add a small delay between sends
                                    usleep(200000); // 200ms

                                } catch (\Exception $e) {
                                    // Log error and create a failed SMS log
                                    SmsLog::create([
                                        'recipient' => $record->recipient,
                                        'message' => $record->message,
                                        'status' => 'failed',
                                        'message_type' => $record->message_type,
                                        'reference_id' => $record->reference_id,
                                        'cost' => $record->cost,
                                        'error_message' => "Resend failed: {$e->getMessage()}",
                                        'sent_by' => Auth::id(),
                                    ]);

                                    $failCount++;
                                }
                            }

                            Notification::make()
                                ->title('Bulk Resend Complete')
                                ->body("Successfully resent: $successCount, Failed: $failCount")
                                ->color($failCount === 0 ? 'success' : 'warning')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Automatically refresh every 30 seconds
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
            //'view' => Pages\ViewSmsLog::route('/{record}'),
        ];
    }

    // Add widgets
    public static function getWidgets(): array
    {
        return [
            //
            // Widgets\SmsDashboardWidget::class,
            // Widgets\SmsTypeDistributionWidget::class,
            // Widgets\DailySmsTrendWidget::class,
            // Widgets\SmsCostOverview::class,
        ];
    }
}
