<?php

namespace App\Filament\Resources\LeaveApplicationResource\Pages;

use App\Filament\Resources\LeaveApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeaveApplication extends EditRecord
{
    protected static string $resource = LeaveApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
