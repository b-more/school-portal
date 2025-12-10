<?php

namespace App\Filament\Resources\GradeSubjectResource\Pages;

use App\Filament\Resources\GradeSubjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGradeSubject extends EditRecord
{
    protected static string $resource = GradeSubjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
