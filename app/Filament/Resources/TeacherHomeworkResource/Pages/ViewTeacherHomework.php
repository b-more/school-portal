<?php

namespace App\Filament\Resources\TeacherHomeworkResource\Pages;

use App\Filament\Resources\TeacherHomeworkResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Grid;
use App\Models\HomeworkSubmission;

class ViewTeacherHomework extends ViewRecord
{
    protected static string $resource = TeacherHomeworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn() => $this->record->status === 'active')
                ->disabled(fn() => $this->record->submissions()->exists()),
            Actions\Action::make('view_submissions')
                ->label('View Submissions')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.index', [
                    'tableFilters' => [
                        'homework' => ['value' => $this->record->id],
                    ],
                ]) ?? '/admin/teacher-homework-submissions')
                ->visible(fn() => $this->record->submissions()->exists()),
            Actions\Action::make('mark_completed')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark homework as completed?')
                ->modalDescription('Once marked as completed, no new submissions will be accepted.')
                ->action(fn () => $this->record->update(['status' => 'completed']))
                ->visible(fn() => $this->record->status === 'active'),
            Actions\DeleteAction::make()
                ->visible(fn() => !$this->record->submissions()->exists()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Homework Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('subject.name')
                                    ->label('Subject')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('grade.name')
                                    ->label('Grade')
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'completed' => 'warning',
                                        'draft' => 'gray',
                                        default => 'primary',
                                    }),
                            ]),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->prose()
                            ->markdown()
                            ->extraAttributes(['class' => 'text-sm']),
                    ]),

                Section::make('Submission Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('submission_start_date')
                                    ->label('Submission Opens')
                                    ->dateTime()
                                    ->placeholder('Not specified'),
                                TextEntry::make('submission_deadline')
                                    ->label('Submission Deadline')
                                    ->dateTime()
                                    ->placeholder('Not specified'),
                                TextEntry::make('max_score')
                                    ->label('Maximum Score')
                                    ->numeric(),
                                TextEntry::make('allow_late_submission')
                                    ->label('Late Submission')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Allowed' : 'Not Allowed'),
                            ]),
                    ]),

                Section::make('Attachments')
                    ->schema([
                        TextEntry::make('homework_files')
                            ->label('Homework Files')
                            ->getStateUsing(fn ($record) => $record->homework_files ? collect($record->homework_files)->map(function ($file) {
                                return [
                                    'name' => pathinfo($file, PATHINFO_BASENAME),
                                    'url' => route('homework.download-file', ['homework' => $record->id, 'file' => $file]),
                                ];
                            })->toArray() : [])
                            ->formatStateUsing(fn ($state) => $state ?
                                view('filament.components.file-list', ['files' => $state])->render() :
                                'No files attached'
                            )
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('additional_resources')
                            ->label('Additional Resources')
                            ->getStateUsing(fn ($record) => $record->additional_resources ? collect($record->additional_resources)->map(function ($file) {
                                return [
                                    'name' => pathinfo($file, PATHINFO_BASENAME),
                                    'url' => route('homework.download-resources', ['homework' => $record->id, 'file' => $file]),
                                ];
                            })->toArray() : [])
                            ->formatStateUsing(fn ($state) => $state ?
                                view('filament.components.file-list', ['files' => $state])->render() :
                                'No additional resources'
                            )
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Submission Statistics')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_submissions')
                                    ->label('Total Submissions')
                                    ->getStateUsing(fn ($record) => $record->submissions()->count())
                                    ->badge()
                                    ->color('primary')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('graded_submissions')
                                    ->label('Graded')
                                    ->getStateUsing(fn ($record) => $record->submissions()->graded()->count())
                                    ->badge()
                                    ->color('success'),
                                TextEntry::make('pending_submissions')
                                    ->label('Pending')
                                    ->getStateUsing(fn ($record) => $record->submissions()->pending()->count())
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('late_submissions')
                                    ->label('Late Submissions')
                                    ->getStateUsing(fn ($record) => $record->submissions()->late()->count())
                                    ->badge()
                                    ->color('danger'),
                                TextEntry::make('average_score')
                                    ->label('Average Score')
                                    ->getStateUsing(fn ($record) => $record->submissions()->graded()->avg('marks'))
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' / ' . $record->max_score : 'N/A')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->submissions()->exists()),

                Section::make('Recent Submissions')
                    ->schema([
                        RepeatableEntry::make('recent_submissions')
                            ->getStateUsing(fn ($record) => $record->submissions()
                                ->with(['student'])
                                ->latest()
                                ->take(5)
                                ->get()
                                ->toArray())
                            ->schema([
                                TextEntry::make('student.name')
                                    ->label('Student'),
                                TextEntry::make('submitted_at')
                                    ->label('Submitted')
                                    ->dateTime(),
                                TextEntry::make('marks')
                                    ->label('Score')
                                    ->formatStateUsing(fn ($state, $record) =>
                                        $record['marks'] !== null ? $record['marks'] . ' / ' . $this->record->max_score : 'Not graded')
                                    ->badge()
                                    ->color(fn ($state, $record) => $record['marks'] !== null ? 'success' : 'warning'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'graded' => 'success',
                                        'submitted' => 'info',
                                        'pending' => 'warning',
                                        default => 'gray',
                                    }),
                            ])->columns(4)
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->submissions()->exists()),
            ]);
    }
}
