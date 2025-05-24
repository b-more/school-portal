<?php

namespace App\Filament\Resources\CommunicationCenterResource\Pages;

use App\Filament\Resources\CommunicationCenterResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use App\Models\SmsLog;

class ViewBroadcast extends ViewRecord
{
    protected static string $resource = CommunicationCenterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewMessages')
                ->label('View Individual Messages')
                ->url(fn () => route('filament.admin.resources.sms-logs.index', [
                    'tableFilters[created_at][from]' => $this->record->started_at?->format('Y-m-d'),
                    'tableFilters[created_at][until]' => $this->record->completed_at?->format('Y-m-d'),
                ]))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('secondary'),

            Actions\Action::make('continue')
                ->label('Continue Sending')
                ->url(fn () => route('filament.admin.resources.communication-center.send-broadcast', ['record' => $this->record]))
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'sending'),
        ];
    }
}
