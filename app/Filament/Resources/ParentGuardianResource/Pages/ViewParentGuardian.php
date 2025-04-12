<?php

namespace App\Filament\Resources\ParentGuardianResource\Pages;

use App\Filament\Resources\ParentGuardianResource;
use App\Services\SmsService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ViewParentGuardian extends ViewRecord
{
    protected static string $resource = ParentGuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('sendSms')
                ->label('Send SMS')
                ->icon('heroicon-o-chat-bubble-left')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Textarea::make('message')
                        ->required()
                        ->default(fn ($record) => "Dear {$record->name}, this is an important message from St. Francis of Assisi School.")
                        ->placeholder('Enter your message here')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    // Get the parent/guardian
                    $parent = $this->record;

                    if (!$parent->phone) {
                        Notification::make()
                            ->title('Cannot send SMS')
                            ->body('No phone number found for this parent/guardian.')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        // Format phone number and send message
                        $formattedPhone = $this->formatPhoneNumber($parent->phone);
                        $this->sendMessage($data['message'], $formattedPhone);

                        // Log success
                        Log::info('SMS sent to parent', [
                            'parent_id' => $parent->id,
                            'phone' => substr($formattedPhone, 0, 6) . '****' . substr($formattedPhone, -3)
                        ]);

                        // Notify admin
                        Notification::make()
                            ->title('SMS Sent')
                            ->body("Message sent to {$parent->name} successfully.")
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        // Log error
                        Log::error('Failed to send SMS', [
                            'parent_id' => $parent->id,
                            'error' => $e->getMessage()
                        ]);

                        // Notify admin
                        Notification::make()
                            ->title('SMS Failed')
                            ->body("Failed to send SMS: {$e->getMessage()}")
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn ($record) => !empty($record->phone)),

            Actions\Action::make('viewStudents')
                ->label('View Children')
                ->icon('heroicon-o-academic-cap')
                ->color('info')
                ->url(fn ($record) => route('filament.admin.resources.students.index', [
                    'tableFilters[parent_guardian_id][value]' => $record->id
                ]))
                ->openUrlInNewTab(),
        ];
    }

    /**
     * Format phone number to ensure it has the country code
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if number already has country code (260 for Zambia)
        if (substr($phoneNumber, 0, 3) === '260') {
            // Number already has country code
            return $phoneNumber;
        }

        // If starting with 0, replace with country code
        if (substr($phoneNumber, 0, 1) === '0') {
            return '260' . substr($phoneNumber, 1);
        }

        // If number doesn't have country code, add it
        if (strlen($phoneNumber) === 9) {
            return '260' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Send a message via SMS
     */
    protected function sendMessage($message_string, $phone_number)
    {
        try {
            // Log the sending attempt
            Log::info('Sending SMS notification', [
                'phone' => $phone_number,
                'message' => substr($message_string, 0, 30) . '...' // Only log beginning of message for privacy
            ]);

            $url_encoded_message = urlencode($message_string);

            $sendSenderSMS = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->post('https://www.cloudservicezm.com/smsservice/httpapi?username=Blessmore&password=Blessmore&msg=' . $url_encoded_message . '&shortcode=2343&sender_id=StFrancis&phone=' . $phone_number . '&api_key=121231313213123123');

            // Log the response
            Log::info('SMS API Response', [
                'status' => $sendSenderSMS->status(),
                'body' => $sendSenderSMS->body(),
                'to' => substr($phone_number, 0, 6) . '****' . substr($phone_number, -3),
            ]);

            return $sendSenderSMS->successful();
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'error' => $e->getMessage(),
                'phone' => $phone_number,
            ]);
            throw $e; // Re-throw to be caught by the calling method
        }
    }
}
