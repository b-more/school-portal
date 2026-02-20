<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInquiry extends CreateRecord
{
    protected static string $resource = InquiryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['logged_by'] = auth()->id();

        return $data;
    }
}
