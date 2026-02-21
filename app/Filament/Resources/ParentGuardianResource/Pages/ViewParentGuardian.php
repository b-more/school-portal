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
                        $smsService = new SmsService();

                        // Check credit first
                        $canSend = $smsService->canSend($data['message']);
                        if (!$canSend['allowed']) {
                            Notification::make()
                                ->title('SMS Failed')
                                ->body($canSend['reason'] ?? 'Insufficient SMS credit.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Send via SmsService
                        $sent = $smsService->send(
                            $data['message'],
                            $parent->phone,
                            'general',
                            $parent->id
                        );

                        if ($sent) {
                            Log::info('SMS sent to parent', [
                                'parent_id' => $parent->id,
                                'phone' => substr($parent->phone, 0, 6) . '****' . substr($parent->phone, -3)
                            ]);

                            Notification::make()
                                ->title('SMS Sent')
                                ->body("Message sent to {$parent->name} successfully.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('SMS Failed')
                                ->body("Failed to send SMS to {$parent->name}. Please check SMS credits and try again.")
                                ->danger()
                                ->send();
                        }

                    } catch (\Exception $e) {
                        Log::error('Failed to send SMS', [
                            'parent_id' => $parent->id,
                            'error' => $e->getMessage()
                        ]);

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
}
