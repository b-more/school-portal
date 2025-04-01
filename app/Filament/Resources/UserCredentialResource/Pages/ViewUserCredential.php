<?php

namespace App\Filament\Resources\UserCredentialResource\Pages;

use App\Filament\Resources\UserCredentialResource;
use App\Services\SmsService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ViewUserCredential extends ViewRecord
{
    protected static string $resource = UserCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resendSms')
                ->label('Resend via SMS')
                ->icon('heroicon-o-phone')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->default(fn () => $this->record->user->phone ?? '')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $message = "Hello {$this->record->user->name}, your account credentials: Username: {$this->record->username}, Password: {$this->record->password}. Please log in and change your password.";

                        SmsService::send($message, $data['phone']);

                        // Update the credential record
                        $this->record->update([
                            'is_sent' => true,
                            'sent_at' => now(),
                            'delivery_method' => 'sms',
                        ]);

                        // Log success
                        Log::info('Credentials resent via SMS', [
                            'user_id' => $this->record->user_id,
                            'username' => $this->record->username,
                            'phone' => $data['phone'],
                        ]);

                        Notification::make()
                            ->title('Credentials Sent')
                            ->body("Login credentials successfully sent to {$data['phone']}")
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Log::error('Failed to resend credentials via SMS', [
                            'user_id' => $this->record->user_id,
                            'username' => $this->record->username,
                            'error' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Error')
                            ->body("Failed to send credentials: {$e->getMessage()}")
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => !$this->record->is_sent || $this->record->delivery_method === 'manual'),

            Actions\Action::make('markRetrieved')
                ->label('Mark Retrieved')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'is_retrieved' => true,
                        'retrieved_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Credentials Retrieved')
                        ->body("Credentials marked as retrieved")
                        ->success()
                        ->send();
                })
                ->visible(fn () => !$this->record->is_retrieved),

            Actions\DeleteAction::make()
                //->visible(fn () => auth()->user()->hasRole('super_admin')),
        ];
    }
}
