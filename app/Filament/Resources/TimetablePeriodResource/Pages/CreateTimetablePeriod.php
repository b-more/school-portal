<?php

namespace App\Filament\Resources\TimetablePeriodResource\Pages;

use App\Filament\Resources\TimetablePeriodResource;
use App\Models\TimetablePeriod;
use Filament\Resources\Pages\CreateRecord;

class CreateTimetablePeriod extends CreateRecord
{
    protected static string $resource = TimetablePeriodResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure order is set if not provided
        if (empty($data['order'])) {
            $data['order'] = TimetablePeriod::getNextOrder($data['academic_year_id']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
