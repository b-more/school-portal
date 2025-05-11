<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeworkSubmissionResource\Pages;
use App\Models\HomeworkSubmission;
use App\Models\Homework;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Result;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class HomeworkSubmissionResource extends Resource
{
    protected static ?string $model = HomeworkSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
{
    return false;
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        Forms\Components\Select::make('homework_id')
                            ->relationship('homework', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('marks', null)),
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Student Comments')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_attachment')
                            ->label('Submission Files')
                            ->directory('homework-submissions')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->required()
                            ->default(now()),
                        Forms\Components\Toggle::make('is_late')
                            ->label('Mark as Late Submission')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Grading')
                    ->schema([
                        Forms\Components\TextInput::make('marks')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->reactive()
                            ->afterStateHydrated(function ($component, $state, $record, callable $set) {
                                // If viewing an existing record and homework exists
                                if ($record && $record->homework) {
                                    $component->maxValue($record->homework->max_score);
                                }
                            })
                            ->afterStateUpdated(function ($state, callable $set, $record, $get) {
                                if ($state) {
                                    $set('status', 'graded');

                                    if (!$get('graded_at')) {
                                        $set('graded_at', Carbon::now());
                                    }

                                    if (!$get('graded_by')) {
                                        $set('graded_by', auth()->id());
                                    }
                                }
                            }),
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'graded' => 'Graded',
                                'returned' => 'Returned',
                            ])
                            ->default('submitted')
                            ->required(),
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback for Student')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('teacher_notes')
                            ->label('Private Teacher Notes')
                            ->helperText('These notes are only visible to teachers, not to students')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('graded_by')
                            ->relationship('gradedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                        Forms\Components\DateTimePicker::make('graded_at')
                            ->default(now()),
                    ])->columns(2),

                Forms\Components\Section::make('Associated Result')
                    ->schema([
                        Forms\Components\Placeholder::make('result_info')
                            ->content(function ($record) {
                                if (!$record || !$record->id) {
                                    return 'Save this submission first to check for associated results.';
                                }

                                $result = Result::where('student_id', $record->student_id)
                                    ->where('homework_id', $record->homework_id)
                                    ->where('exam_type', 'assignment')
                                    ->first();

                                if (!$result) {
                                    return 'No result record has been created for this submission yet. After grading, you can create a result record from the actions menu.';
                                }

                                return "Result Record: {$result->grade} ({$result->marks}%) - Created on " . $result->created_at->format('M d, Y H:i');
                            })
                    ])
                    ->visible(function ($record) {
                        return $record && $record->id;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('homework.title')
                    ->label('Homework')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.grade')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (HomeworkSubmission $record) => $record->is_late ? "{$record->status} (Late)" : $record->status)
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'info' => 'returned',
                    ]),
                Tables\Columns\TextColumn::make('marks')
                    ->formatStateUsing(function (HomeworkSubmission $record) {
                        if ($record->marks === null) {
                            return '-';
                        }

                        $maxScore = $record->homework?->max_score ?? 100;
                        return "{$record->marks}/{$maxScore} (" . round(($record->marks / $maxScore) * 100) . "%)";
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_attachment')
                    ->label('Files')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_attachment)),
                Tables\Columns\IconColumn::make('has_result')
                    ->label('Result Created')
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return Result::where('student_id', $record->student_id)
                            ->where('homework_id', $record->homework_id)
                            ->where('exam_type', 'assignment')
                            ->exists();
                    }),
                Tables\Columns\TextColumn::make('gradedBy.name')
                    ->label('Graded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('homework')
                    ->relationship('homework', 'title'),
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'returned' => 'Returned',
                    ]),
                Tables\Filters\Filter::make('is_late')
                    ->query(fn (Builder $query): Builder => $query->where('is_late', true))
                    ->label('Late Submissions')
                    ->toggle(),
                Tables\Filters\Filter::make('has_marks')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('marks'))
                    ->label('Graded Submissions')
                    ->toggle(),
                Tables\Filters\Filter::make('no_marks')
                    ->query(fn (Builder $query): Builder => $query->whereNull('marks'))
                    ->label('Ungraded Submissions')
                    ->toggle(),
                Tables\Filters\Filter::make('has_result')
                    ->label('Has Result Record')
                    ->query(function (Builder $query): Builder {
                        $submissionIds = $query->pluck('id');
                        $withResults = Result::where('exam_type', 'assignment')
                            ->whereIn('student_id', function ($q) use ($submissionIds) {
                                $q->select('student_id')->from('homework_submissions')->whereIn('id', $submissionIds);
                            })
                            ->whereIn('homework_id', function ($q) use ($submissionIds) {
                                $q->select('homework_id')->from('homework_submissions')->whereIn('id', $submissionIds);
                            })
                            ->pluck('student_id', 'homework_id')
                            ->toArray();

                        return $query->where(function ($q) use ($withResults) {
                            foreach ($withResults as $homeworkId => $studentId) {
                                $q->orWhere(function ($sq) use ($homeworkId, $studentId) {
                                    $sq->where('homework_id', $homeworkId)
                                       ->where('student_id', $studentId);
                                });
                            }
                        });
                    })
                    ->toggle(),
                Tables\Filters\Filter::make('no_result')
                    ->label('No Result Record')
                    ->query(function (Builder $query): Builder {
                        $submissionIds = $query->pluck('id');
                        $withResults = Result::where('exam_type', 'assignment')
                            ->whereIn('student_id', function ($q) use ($submissionIds) {
                                $q->select('student_id')->from('homework_submissions')->whereIn('id', $submissionIds);
                            })
                            ->whereIn('homework_id', function ($q) use ($submissionIds) {
                                $q->select('homework_id')->from('homework_submissions')->whereIn('id', $submissionIds);
                            })
                            ->pluck('student_id', 'homework_id')
                            ->toArray();

                        return $query->where(function ($q) use ($withResults) {
                            foreach ($withResults as $homeworkId => $studentId) {
                                $q->whereNot(function ($sq) use ($homeworkId, $studentId) {
                                    $sq->where('homework_id', $homeworkId)
                                       ->where('student_id', $studentId);
                                });
                            }
                        });
                    })
                    ->toggle(),
                Tables\Filters\Filter::make('submitted_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('submitted_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('grade')
                    ->label('Grade Submission')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\TextInput::make('marks')
                            ->label('Score')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(fn ($record) => $record->homework?->max_score ?? 100),
                        Forms\Components\Textarea::make('feedback')
                            ->required(),
                        Forms\Components\Textarea::make('teacher_notes')
                            ->label('Private Notes (Teacher Only)')
                            ->helperText('These notes won\'t be visible to students'),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'marks' => $data['marks'],
                            'feedback' => $data['feedback'],
                            'teacher_notes' => $data['teacher_notes'] ?? null,
                            'status' => 'graded',
                            'graded_by' => auth()->id(),
                            'graded_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Submission graded successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'submitted'),
                Tables\Actions\Action::make('createResult')
                    ->label('Create Result')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('success')
                    ->action(function ($record): void {
                        // Only proceed if the submission has been graded
                        if ($record->marks === null) {
                            Notification::make()
                                ->title('Cannot Create Result')
                                ->body('The submission must be graded before creating a result record.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Check if result already exists
                        $existingResult = Result::where('student_id', $record->student_id)
                            ->where('exam_type', 'assignment')
                            ->where('homework_id', $record->homework_id)
                            ->first();

                        if ($existingResult) {
                            Notification::make()
                                ->title('Result Already Exists')
                                ->body('A result record for this homework assignment already exists.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Get homework and student details
                        $homework = $record->homework;
                        $student = $record->student;

                        if (!$homework || !$student) {
                            Notification::make()
                                ->title('Missing Information')
                                ->body('Cannot create result due to missing homework or student information.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Create corresponding result record
                        $result = Result::create([
                            'student_id' => $student->id,
                            'subject_id' => $homework->subject_id,
                            'exam_type' => 'assignment',
                            'homework_id' => $homework->id,
                            'marks' => $record->marks,
                            'grade' => self::getGradeFromMarks($record->marks),
                            'term' => 'first', // Default - you may want to set this dynamically
                            'year' => date('Y'),
                            'comment' => $record->feedback,
                            'recorded_by' => $record->graded_by ?? auth()->id(),
                            'notify_parent' => true,
                        ]);

                        // Show success notification
                        Notification::make()
                            ->title('Result Created')
                            ->body('Result record has been created successfully.')
                            ->success()
                            ->send();

                        // Redirect to the result edit page
                        redirect()->route('filament.admin.resources.results.edit', ['record' => $result->id]);
                    })
                    ->visible(fn ($record) =>
                        $record->marks !== null &&
                        !Result::where('student_id', $record->student_id)
                            ->where('exam_type', 'assignment')
                            ->where('homework_id', $record->homework_id)
                            ->exists()
                    ),
                Tables\Actions\Action::make('viewResult')
                    ->label('View Result')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('primary')
                    ->action(function ($record): void {
                        $result = Result::where('student_id', $record->student_id)
                            ->where('exam_type', 'assignment')
                            ->where('homework_id', $record->homework_id)
                            ->first();

                        if (!$result) {
                            Notification::make()
                                ->title('No Result Found')
                                ->body('No result record exists for this submission.')
                                ->warning()
                                ->send();
                            return;
                        }

                        redirect()->route('filament.admin.resources.results.view', ['record' => $result->id]);
                    })
                    ->visible(fn ($record) =>
                        Result::where('student_id', $record->student_id)
                            ->where('exam_type', 'assignment')
                            ->where('homework_id', $record->homework_id)
                            ->exists()
                    ),
                Tables\Actions\Action::make('download')
                    ->label('Download Files')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.resources.homework-submissions.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->file_attachment)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_grade')
                        ->label('Bulk Mark as Graded')
                        ->icon('heroicon-o-check')
                        ->action(function (Builder $query) {
                            // Update all selected records to graded status
                            $query->update([
                                'status' => 'graded',
                                'graded_by' => auth()->id(),
                                'graded_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Submissions marked as graded')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('bulk_create_results')
                        ->label('Create Results for Graded')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->color('success')
                        ->action(function (Builder $query) {
                            // Get all graded submissions that don't have results yet
                            $submissions = $query->whereNotNull('marks')
                                ->where('status', 'graded')
                                ->get();

                            $createdCount = 0;
                            $errorCount = 0;

                            foreach ($submissions as $submission) {
                                // Check if result already exists
                                $existingResult = Result::where('student_id', $submission->student_id)
                                    ->where('exam_type', 'assignment')
                                    ->where('homework_id', $submission->homework_id)
                                    ->first();

                                if ($existingResult) {
                                    $errorCount++;
                                    continue; // Skip if result exists
                                }

                                // Get homework and student details
                                $homework = $submission->homework;
                                $student = $submission->student;

                                if (!$homework || !$student) {
                                    $errorCount++;
                                    continue;
                                }

                                try {
                                    // Create corresponding result record
                                    Result::create([
                                        'student_id' => $student->id,
                                        'subject_id' => $homework->subject_id,
                                        'exam_type' => 'assignment',
                                        'homework_id' => $homework->id,
                                        'marks' => $submission->marks,
                                        'grade' => self::getGradeFromMarks($submission->marks),
                                        'term' => 'first', // Default
                                        'year' => date('Y'),
                                        'comment' => $submission->feedback,
                                        'recorded_by' => $submission->graded_by ?? auth()->id(),
                                        'notify_parent' => true,
                                    ]);

                                    $createdCount++;
                                } catch (\Exception $e) {
                                    $errorCount++;
                                }
                            }

                            Notification::make()
                                ->title('Result Creation Complete')
                                ->body("Created: {$createdCount}, Failed: {$errorCount}")
                                ->success($createdCount > 0)
                                ->warning($errorCount > 0)
                                ->send();
                        }),
                ]),
            ]);
    }

    /**
     * Determine letter grade from numerical marks
     */
    protected static function getGradeFromMarks($marks) {
        if ($marks >= 90) return 'A+';
        if ($marks >= 80) return 'A';
        if ($marks >= 70) return 'B';
        if ($marks >= 60) return 'C';
        if ($marks >= 50) return 'D';
        return 'F';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomeworkSubmissions::route('/'),
            'create' => Pages\CreateHomeworkSubmission::route('/create'),
            'view' => Pages\ViewHomeworkSubmission::route('/{record}'),
            'edit' => Pages\EditHomeworkSubmission::route('/{record}/edit'),
        ];
    }
}
