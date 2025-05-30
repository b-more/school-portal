<?php

namespace App\Filament\Resources\SchoolClassResource\Pages;

use App\Filament\Resources\SchoolClassResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Illuminate\Support\Facades\DB;
use App\Models\Subject;
use App\Models\Teacher;

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
                        Components\TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Components\Section::make('Class Teachers')
                    ->schema([
                        Components\RepeatableEntry::make('teachers')
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label('Teacher Name'),
                                Components\TextEntry::make('employee_id')
                                    ->label('Employee ID'),
                                Components\TextEntry::make('specialization')
                                    ->label('Specialization')
                                    ->placeholder('Primary Teacher'),
                                Components\TextEntry::make('pivot.role')
                                    ->label('Role in Class')
                                    ->badge()
                                    ->color(function ($state) {
                                        return match($state) {
                                            'class_teacher' => 'success',
                                            'assistant_teacher' => 'info',
                                            'subject_teacher' => 'warning',
                                            default => 'gray',
                                        };
                                    })
                                    ->formatStateUsing(function ($state) {
                                        return match($state) {
                                            'class_teacher' => 'Class Teacher',
                                            'assistant_teacher' => 'Assistant Teacher',
                                            'subject_teacher' => 'Subject Teacher',
                                            default => ucfirst(str_replace('_', ' ', $state)),
                                        };
                                    }),
                                Components\IconEntry::make('pivot.is_primary')
                                    ->boolean()
                                    ->label('Primary Teacher'),
                                Components\TextEntry::make('phone')
                                    ->label('Contact'),
                            ])
                            ->columns(6),
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
                                            ->where('teacher_id', $record->id)
                                            ->value('subject_id');

                                        return Subject::find($subjectId)?->name ?? 'Unknown';
                                    }),
                                Components\TextEntry::make('name')
                                    ->label('Teacher'),
                                Components\TextEntry::make('employee_id')
                                    ->label('Employee ID'),
                                Components\TextEntry::make('specialization')
                                    ->label('Specialization'),
                                Components\TextEntry::make('phone')
                                    ->label('Contact'),
                            ])
                            ->columns(5)
                    ])
                    ->visible(fn ($record) => $record->department === 'Secondary'),

                Components\Section::make('Class Statistics')
                    ->schema([
                        Components\TextEntry::make('teachers_count')
                            ->label('Total Teachers')
                            ->getStateUsing(fn ($record) => $record->teachers()->count()),
                        Components\TextEntry::make('subject_teachers_count')
                            ->label('Subject Teachers')
                            ->getStateUsing(function ($record) {
                                return DB::table('class_subject_teacher')
                                    ->where('class_id', $record->id)
                                    ->distinct('teacher_id')
                                    ->count('teacher_id');
                            }),
                        Components\TextEntry::make('primary_teachers_count')
                            ->label('Primary Teachers')
                            ->getStateUsing(function ($record) {
                                return $record->teachers()
                                    ->wherePivot('is_primary', true)
                                    ->count();
                            }),
                        Components\TextEntry::make('class_teachers_count')
                            ->label('Class Teachers')
                            ->getStateUsing(function ($record) {
                                return $record->teachers()
                                    ->wherePivot('role', 'class_teacher')
                                    ->count();
                            }),
                    ])
                    ->columns(4),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),

            \Filament\Actions\Action::make('view_all_assignments')
                ->label('View All Assignments')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('info')
                ->modalHeading('All Teacher Assignments')
                ->modalContent(function () {
                    $teachers = $this->record->teachers()->get();

                    if ($teachers->isEmpty()) {
                        return new \Illuminate\Support\HtmlString('<p>No teachers assigned to this class.</p>');
                    }

                    $content = "<div class='space-y-6'>";

                    foreach ($teachers as $teacher) {
                        $content .= "<div class='border rounded-lg p-4'>";
                        $content .= "<h3 class='font-semibold text-lg'>{$teacher->name} ({$teacher->employee_id})</h3>";
                        $content .= "<p class='text-sm text-gray-600 mb-2'>";
                        $content .= "Type: " . ($teacher->isPrimaryTeacher() ? 'Primary' : 'Secondary');
                        if ($teacher->specialization) {
                            $content .= " | Specialization: {$teacher->specialization}";
                        }
                        $content .= "</p>";

                        // Get role in this class
                        $role = $teacher->pivot->role ?? 'N/A';
                        $isPrimary = $teacher->pivot->is_primary ? 'Yes' : 'No';

                        $content .= "<div class='grid grid-cols-2 gap-4 text-sm mb-3'>";
                        $content .= "<div><strong>Role in Class:</strong> " . ucfirst(str_replace('_', ' ', $role)) . "</div>";
                        $content .= "<div><strong>Primary Teacher:</strong> {$isPrimary}</div>";
                        $content .= "</div>";

                        // Get subject assignments for this teacher in this class
                        $subjectAssignments = DB::table('class_subject_teacher')
                            ->join('subjects', 'subjects.id', '=', 'class_subject_teacher.subject_id')
                            ->where('class_subject_teacher.class_id', $this->record->id)
                            ->where('class_subject_teacher.teacher_id', $teacher->id)
                            ->pluck('subjects.name')
                            ->toArray();

                        if (!empty($subjectAssignments)) {
                            $content .= "<div class='text-sm'>";
                            $content .= "<strong>Subjects in this class:</strong> " . implode(', ', $subjectAssignments);
                            $content .= "</div>";
                        }

                        // Get overall teaching summary if available
                        if (method_exists($teacher, 'getTeachingSummary')) {
                            $summary = $teacher->getTeachingSummary();
                            $content .= "<div class='grid grid-cols-2 gap-4 text-sm mt-2 pt-2 border-t'>";
                            $content .= "<div><strong>Total Students:</strong> {$summary['total_students']}</div>";
                            $content .= "<div><strong>Total Subjects:</strong> {$summary['total_subjects']}</div>";
                            $content .= "</div>";
                        }

                        $content .= "</div>";
                    }

                    $content .= "</div>";
                    return new \Illuminate\Support\HtmlString($content);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
        ];
    }
}
