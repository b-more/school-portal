<?php

namespace App\Filament\Resources\InquiryResource\Pages;

use App\Filament\Resources\InquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInquiry extends EditRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? '', ['responded', 'closed']) && empty($this->record->responded_by)) {
            $data['responded_by'] = auth()->id();
            $data['responded_at'] = now();
        }

        return $data;
    }
}
