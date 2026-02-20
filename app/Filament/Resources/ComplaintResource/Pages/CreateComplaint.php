<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['logged_by'] = auth()->id();

        return $data;
    }
}
