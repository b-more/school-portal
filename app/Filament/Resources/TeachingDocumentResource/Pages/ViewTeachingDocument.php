<?php

namespace App\Filament\Resources\TeachingDocumentResource\Pages;

use App\Filament\Resources\TeachingDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewTeachingDocument extends ViewRecord
{
    protected static string $resource = TeachingDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => Storage::disk('public')->url($this->record->file_path))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->file_path) {
                        Storage::disk('public')->delete($this->record->file_path);
                    }
                }),
        ];
    }
}
