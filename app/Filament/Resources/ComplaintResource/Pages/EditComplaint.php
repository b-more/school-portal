<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComplaint extends EditRecord
{
    protected static string $resource = ComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (in_array($data['status'] ?? '', ['resolved', 'closed']) && empty($this->record->resolved_by)) {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
