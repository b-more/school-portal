<?php

namespace App\Filament\Resources\TimetablePeriodResource\Pages;

use App\Filament\Resources\TimetablePeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimetablePeriod extends EditRecord
{
    protected static string $resource = TimetablePeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->timetableEntries()->count() > 0) {
                        throw new \Exception('Cannot delete period that has timetable entries. Remove entries first.');
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
