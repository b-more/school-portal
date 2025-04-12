<?php

namespace App\Filament\Resources\SchoolClassResource\Pages;

use App\Filament\Resources\SchoolClassResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\Employee;

class ViewSchoolClass extends ViewRecord
{
    protected static string $resource = SchoolClassResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Class Information')
                    ->schema([
                        Components\TextEntry::make('name')
                            ->label('Class Name'),
                        Components\TextEntry::make('department'),
                        Components\TextEntry::make('grade'),
                        Components\TextEntry::make('section'),
                        Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Active Status'),
                    ])
                    ->columns(3),

                Components\Section::make('Class Teachers')
                    ->schema([
                        Components\RepeatableEntry::make('teachers')
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('Teacher Name'),
                                Components\TextEntry::make('position')
                                    ->label('Position'),
                                Components\TextEntry::make('pivot.role')
                                    ->label('Role in Class')
                                    ->badge(),
                                Components\IconEntry::make('pivot.is_primary')
                                    ->boolean()
                                    ->label('Primary Teacher'),
                                Components\TextEntry::make('phone')
                                    ->label('Contact'),
                            ])
                            ->columns(5),
                    ]),

                Components\Section::make('Subject Teachers')
                    ->schema([
                        Components\RepeatableEntry::make('subjectTeachers')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('subject_name')
                                    ->label('Subject')
                                    ->getStateUsing(function ($record) {
                                        $subjectId = DB::table('class_subject_teacher')
                                            ->where('class_id', $this->record->id)
                                            ->where('employee_id', $record->id)
                                            ->value('subject_id');

                                        return Subject::find($subjectId)?->name ?? 'Unknown';
                                    }),
                                Components\TextEntry::make('name')
                                    ->label('Teacher'),
                                Components\TextEntry::make('phone')
                                    ->label('Contact'),
                            ])
                            ->columns(3)
                    ])
                    ->visible(fn ($record) => $record->department === 'Secondary'),
            ]);
    }
}
