<?php

namespace App\Filament\Resources\TeacherAssignmentResource\Pages;

use App\Filament\Resources\TeacherAssignmentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewTeacherAssignment extends ViewRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Teacher Information')
                    ->schema([
                        Components\TextEntry::make('name')
                            ->label('Teacher Name'),
                        Components\TextEntry::make('department'),
                        Components\TextEntry::make('position'),
                    ])
                    ->columns(3),

                // For ECL/Primary
                Components\Section::make('Class Assignments')
                    ->schema([
                        Components\RepeatableEntry::make('classes')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('Class Name'),
                                Components\TextEntry::make('pivot.role')
                                    ->label('Role'),
                            ])
                            ->columns(2)
                    ])
                    ->visible(fn ($record) => in_array($record->department, ['ECL', 'Primary'])),

                // For Secondary
                Components\Section::make('Subject Assignments')
                    ->schema([
                        Components\RepeatableEntry::make('subjects')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('Subject Name'),
                            ])
                    ])
                    ->visible(fn ($record) => $record->department === 'Secondary'),

                Components\Section::make('Class-Subject Assignments')
                    ->schema([
                        Components\RepeatableEntry::make('classSubjectAssignments')
                            ->label('')
                            ->schema([
                                Components\TextEntry::make('subject.name')
                                    ->label('Subject'),
                                Components\TextEntry::make('class.name')
                                    ->label('Class'),
                            ])
                            ->columns(2)
                    ])
                    ->visible(fn ($record) => $record->department === 'Secondary')
                    ->getStateUsing(function ($record) {
                        $assignments = [];

                        $classSubjects = \DB::table('class_subject_teacher')
                            ->where('employee_id', $record->id)
                            ->get();

                        foreach ($classSubjects as $assignment) {
                            $assignments[] = [
                                'subject' => [
                                    'name' => \App\Models\Subject::find($assignment->subject_id)->name ?? 'Unknown'
                                ],
                                'class' => [
                                    'name' => \App\Models\SchoolClass::find($assignment->class_id)->name ?? 'Unknown'
                                ]
                            ];
                        }

                        return $assignments;
                    }),
            ]);
    }
}
