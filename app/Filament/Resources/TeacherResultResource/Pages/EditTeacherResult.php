<?php

namespace App\Filament\Resources\TeacherResultResource\Pages;

use App\Filament\Resources\TeacherResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherResult extends EditRecord
{
    protected static string $resource = TeacherResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
