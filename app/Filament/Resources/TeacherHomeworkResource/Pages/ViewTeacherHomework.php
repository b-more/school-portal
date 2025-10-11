<?php

namespace App\Filament\Resources\TeacherHomeworkResource\Pages;

use App\Constants\RoleConstants;
use App\Filament\Resources\TeacherHomeworkResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewTeacherHomework extends ViewRecord
{
    protected static string $resource = TeacherHomeworkResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $isStudent = $user->role_id === RoleConstants::STUDENT;
        $isTeacherOrAdmin = in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);

        // Students see different actions than teachers
        if ($isStudent) {
            return [
                Actions\Action::make('submit_homework')
                    ->label('Submit Homework')
                    ->icon('heroicon-o-paper-clip')
                    ->color('primary')
                    ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.create', [
                        'homework_id' => $this->record->id,
                    ]))
                    ->visible(fn () => $this->record->status === 'active' && ! $this->record->isSubmittedByStudent($this->getStudentId())),
                Actions\Action::make('view_my_submission')
                    ->label('View My Submission')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.index'))
                    ->visible(fn () => $this->record->isSubmittedByStudent($this->getStudentId())),
            ];
        }

        // Teachers and admins see management actions
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->status === 'active' && $isTeacherOrAdmin)
                ->disabled(fn () => $this->record->submissions()->exists()),
            Actions\Action::make('view_submissions')
                ->label('View Submissions')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.teacher-homework-submissions.index', [
                    'tableFilters' => [
                        'homework' => ['value' => $this->record->id],
                    ],
                ]) ?? '/admin/teacher-homework-submissions')
                ->visible(fn () => $this->record->submissions()->exists() && $isTeacherOrAdmin),
            Actions\Action::make('mark_completed')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark homework as completed?')
                ->modalDescription('Once marked as completed, no new submissions will be accepted.')
                ->action(fn () => $this->record->update(['status' => 'completed']))
                ->visible(fn () => $this->record->status === 'active' && $isTeacherOrAdmin),
            Actions\DeleteAction::make()
                ->visible(fn () => ! $this->record->submissions()->exists() && $isTeacherOrAdmin),
        ];
    }

    protected function getStudentId(): ?int
    {
        $student = \App\Models\Student::where('user_id', auth()->id())->first();

        return $student?->id;
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
                                TextEntry::make('submission_start')
                                    ->label('Submission Opens')
                                    ->dateTime()
                                    ->placeholder('Not specified'),
                                TextEntry::make('submission_end')
                                    ->label('Submission Deadline')
                                    ->dateTime()
                                    ->placeholder('Not specified'),
                                TextEntry::make('due_date')
                                    ->label('Due Date')
                                    ->date()
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
                        TextEntry::make('homework_file')
                            ->label('Homework Document')
                            ->getStateUsing(function ($record) {
                                if (!$record->homework_file) {
                                    return 'No homework document attached';
                                }

                                $fileName = pathinfo($record->homework_file, PATHINFO_BASENAME);
                                $downloadUrl = route('homework.download', ['homework' => $record->id]);
                                $viewUrl = route('homework.view', ['homework' => $record->id]);

                                return '<div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">' . pathinfo($record->homework_file, PATHINFO_FILENAME) . '.pdf</p>
                                        <p class="text-xs text-gray-500">PDF Document</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="' . $viewUrl . '" target="_blank" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-md dark:bg-blue-900 dark:text-blue-300">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        <a href="' . $downloadUrl . '" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-md">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>';
                            })
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('submission_instructions')
                            ->label('Submission Instructions')
                            ->default('No specific instructions provided')
                            ->columnSpanFull()
                            ->visible(fn ($record) => !empty($record->submission_instructions)),
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
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2).' / '.$this->record->max_score : 'N/A')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->submissions()->exists() && in_array(auth()->user()->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER])),

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
                                    ->formatStateUsing(fn ($state, $record) => $record['marks'] !== null ? $record['marks'].' / '.$this->record->max_score : 'Not graded')
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
                            ])->columns(4),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->submissions()->exists() && in_array(auth()->user()->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER])),
            ]);
    }
}
