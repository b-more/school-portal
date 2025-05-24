<?php

namespace App\Filament\Resources\SmsLogResource\Pages;

use App\Filament\Resources\SmsLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewSmsLog extends ViewRecord
{
    protected static string $resource = SmsLogResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Message Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date & Time')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('recipient')
                            ->label('Phone Number')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('message')
                            ->label('Message Content')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('message_type')
                            ->label('Message Type')
                            ->badge(),
                        Infolists\Components\TextEntry::make('reference_id')
                            ->label('Reference ID')
                            ->default('None'),
                    ]),

                Infolists\Components\Section::make('Status Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'delivered' => 'success',
                                'sent' => 'primary',
                                'failed' => 'danger',
                                'pending' => 'warning',
                                default => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('cost')
                            ->money('ZMW'),
                        Infolists\Components\TextEntry::make('sender.name')
                            ->label('Sent By'),
                    ]),

                Infolists\Components\Section::make('Technical Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('provider_reference')
                            ->label('Provider Reference')
                            ->default('None'),
                        Infolists\Components\TextEntry::make('error_message')
                            ->label('Error Message')
                            ->default('None')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resend')
                ->label('Resend Message')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->status !== 'pending')
                ->action(function () {
                    $record = $this->record;

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
        ];
    }
}
