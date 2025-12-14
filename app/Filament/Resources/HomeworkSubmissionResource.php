<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\HomeworkSubmissionResource\Pages;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Result;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HomeworkSubmissionResource extends Resource
{
    protected static ?string $model = HomeworkSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER, RoleConstants::STUDENT, RoleConstants::PARENT]);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isStudent = $user->role_id === RoleConstants::STUDENT;
        $isParent = $user->role_id === RoleConstants::PARENT;
        $isTeacher = in_array($user->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()]);

        // Get student record if current user is a student
        $currentStudent = null;
        if ($isStudent) {
            $currentStudent = Student::where('user_id', $user->id)->first();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        // Hidden field for student submissions - auto-set to their own ID
                        Forms\Components\Hidden::make('student_id')
                            ->default($currentStudent?->id)
                            ->visible($isStudent),

                        // Select field for teachers/parents/admins
                        Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->options(function () use ($user, $isParent) {
                                if ($isParent) {
                                    // Parents see only their children
                                    $parent = $user->parentGuardian;
                                    if ($parent) {
                                        return $parent->students()
                                            ->where('enrollment_status', 'active')
                                            ->pluck('name', 'id');
                                    }
                                    return [];
                                }
                                // Teachers and admins see all students
                                return Student::where('enrollment_status', 'active')
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('homework_id', null))
                            ->visible(!$isStudent),

                        // For students, show their name as a placeholder
                        Forms\Components\Placeholder::make('student_name')
                            ->label('Submitting as')
                            ->content($currentStudent?->name ?? 'Unknown Student')
                            ->visible($isStudent),

                        Forms\Components\Select::make('homework_id')
                            ->label('Homework Assignment')
                            ->options(function (callable $get) use ($isStudent, $currentStudent) {
                                // For students, use their own student ID
                                $studentId = $isStudent ? $currentStudent?->id : $get('student_id');
                                if (!$studentId) {
                                    return [];
                                }

                                $student = Student::find($studentId);
                                if (!$student) {
                                    return [];
                                }

                                // Get homework IDs that already have submissions from this student
                                $submittedHomeworkIds = HomeworkSubmission::where('student_id', $studentId)
                                    ->pluck('homework_id')
                                    ->toArray();

                                // Get homework for the student's grade that is still active and not yet submitted
                                return Homework::where('grade_id', $student->grade_id)
                                    ->where('status', 'active')
                                    ->where('due_date', '>=', now()->subDays(7)) // Show homework from last 7 days
                                    ->whereNotIn('id', $submittedHomeworkIds) // Exclude already submitted
                                    ->orderBy('due_date', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($homework) {
                                        $dueDate = $homework->due_date->format('M d, Y');
                                        $subject = $homework->subject?->name ?? 'Unknown';
                                        $status = $homework->due_date->isPast() ? ' [OVERDUE]' : '';
                                        return [$homework->id => "{$homework->title} - {$subject} (Due: {$dueDate}){$status}"];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('marks', null))
                            ->helperText('Select which homework assignment you are submitting (already submitted homework is not shown)'),
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
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->required()
                            ->default(now())
                            ->visible(!$isStudent), // Hidden for students - auto-set
                        Forms\Components\Hidden::make('submitted_at')
                            ->default(now())
                            ->visible($isStudent),
                        Forms\Components\Toggle::make('is_late')
                            ->label('Mark as Late Submission')
                            ->default(false)
                            ->visible(!$isStudent), // Hidden for students - system calculated
                    ])->columns(2),

                // Hidden status field for students - auto-set to submitted
                Forms\Components\Hidden::make('status')
                    ->default('submitted')
                    ->visible($isStudent),

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

                                    if (! $get('graded_at')) {
                                        $set('graded_at', Carbon::now());
                                    }

                                    if (! $get('graded_by')) {
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
                            ->columnSpanFull()
                            ->disabled($isParent),
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
                    ])
                    ->columns(2)
                    ->visible($isTeacher), // Only teachers and admins can see grading section

                Forms\Components\Section::make('Associated Result')
                    ->schema([
                        Forms\Components\Placeholder::make('result_info')
                            ->content(function ($record) {
                                if (! $record || ! $record->id) {
                                    return 'Save this submission first to check for associated results.';
                                }

                                $result = Result::where('student_id', $record->student_id)
                                    ->where('homework_id', $record->homework_id)
                                    ->where('exam_type', 'assignment')
                                    ->first();

                                if (! $result) {
                                    return 'No result record has been created for this submission yet. After grading, you can create a result record from the actions menu.';
                                }

                                return "Result Record: {$result->grade} ({$result->marks}%) - Created on ".$result->created_at->format('M d, Y H:i');
                            }),
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
                Tables\Columns\TextColumn::make('student.grade.name')
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

                        return "{$record->marks}/{$maxScore} (".round(($record->marks / $maxScore) * 100).'%)';
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_attachment')
                    ->label('Files')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->file_attachment)),
                Tables\Columns\IconColumn::make('has_result')
                    ->label('Result Created')
                    ->boolean()
                    ->getStateUsing(fn ($record) => (bool) $record->has_result),
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
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn () => in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()->role_id === RoleConstants::ADMIN),
                // Student cancel submission action - only before grading and before due date
                Tables\Actions\Action::make('cancelSubmission')
                    ->label('Cancel Submission')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Homework Submission')
                    ->modalDescription('Are you sure you want to cancel this submission? You can submit again before the due date.')
                    ->modalSubmitActionLabel('Yes, Cancel Submission')
                    ->action(function ($record): void {
                        // Delete the submission
                        $record->delete();

                        Notification::make()
                            ->title('Submission Cancelled')
                            ->body('Your submission has been cancelled. You can now submit again.')
                            ->success()
                            ->send();
                    })
                    ->visible(function ($record) {
                        $user = Auth::user();
                        // Only for students
                        if ($user->role_id !== RoleConstants::STUDENT) {
                            return false;
                        }
                        // Only if not graded
                        if ($record->status === 'graded' || $record->marks !== null) {
                            return false;
                        }
                        // Only before due date (if homework exists)
                        if ($record->homework && $record->homework->due_date && $record->homework->due_date->isPast()) {
                            return false;
                        }
                        // If no homework found, don't show button
                        if (!$record->homework) {
                            return false;
                        }
                        return true;
                    }),
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
                            ->maxValue(fn ($record) => $record->homework?->max_score ?? 100)
                            ->suffix(fn ($record) => '/ ' . ($record->homework?->max_score ?? 100))
                            ->helperText(fn ($record) => 'Enter score out of ' . ($record->homework?->max_score ?? 100) . '. Example: Enter 75 for 75%'),
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
                    ->visible(fn ($record) => $record->status === 'submitted' && in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
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

                        if (! $homework || ! $student) {
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
                    ->visible(fn ($record) => $record->marks !== null && ! $record->has_result && in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
                Tables\Actions\Action::make('viewResult')
                    ->label('View Result')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('primary')
                    ->action(function ($record): void {
                        $result = Result::where('student_id', $record->student_id)
                            ->where('exam_type', 'assignment')
                            ->where('homework_id', $record->homework_id)
                            ->first();

                        if (! $result) {
                            Notification::make()
                                ->title('No Result Found')
                                ->body('No result record exists for this submission.')
                                ->warning()
                                ->send();

                            return;
                        }

                        redirect()->route('filament.admin.resources.results.view', ['record' => $result->id]);
                    })
                    ->visible(fn ($record) => (bool) $record->has_result && in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
                Tables\Actions\Action::make('download')
                    ->label('Download Files')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.resources.homework-submissions.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! empty($record->file_attachment) && in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->role_id === RoleConstants::ADMIN),
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
                        })
                        ->visible(fn () => in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
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

                                if (! $homework || ! $student) {
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
                        })
                        ->visible(fn () => in_array(Auth::user()->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])),
                ]),
            ]);
    }

    /**
     * Determine letter grade from numerical marks (percentage)
     * Grade Scale:
     * A+ : 90-100%
     * A  : 80-89%
     * B+ : 75-79%
     * B  : 70-74%
     * C+ : 65-69%
     * C  : 60-64%
     * D+ : 55-59%
     * D  : 50-54%
     * E  : 40-49%
     * F  : Below 40% (Fail)
     */
    protected static function getGradeFromMarks($marks)
    {
        if ($marks >= 90) {
            return 'A+';
        }
        if ($marks >= 80) {
            return 'A';
        }
        if ($marks >= 75) {
            return 'B+';
        }
        if ($marks >= 70) {
            return 'B';
        }
        if ($marks >= 65) {
            return 'C+';
        }
        if ($marks >= 60) {
            return 'C';
        }
        if ($marks >= 55) {
            return 'D+';
        }
        if ($marks >= 50) {
            return 'D';
        }
        if ($marks >= 40) {
            return 'E';
        }

        return 'F'; // Below 40% is a Fail
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->select('homework_submissions.*')
            ->with([
                'homework:id,title,max_score,subject_id,grade_id',
                'student:id,name,grade_id',
                'student.grade:id,name',
                'gradedBy:id,name',
            ])
            ->addSelect([
                'has_result' => Result::select(\DB::raw('1'))
                    ->whereColumn('results.student_id', 'homework_submissions.student_id')
                    ->whereColumn('results.homework_id', 'homework_submissions.homework_id')
                    ->where('results.exam_type', 'assignment')
                    ->limit(1),
            ]);

        $user = Auth::user();

        // Admin and teachers can see all submissions
        if (in_array($user->role_id, [RoleConstants::ADMIN, ...RoleConstants::teaching()])) {
            return $query;
        }

        // Parents can only see their children's submissions
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;

            if ($parent) {
                $studentIds = $parent->students()
                    ->where('enrollment_status', 'active')
                    ->pluck('id')
                    ->toArray();

                if (!empty($studentIds)) {
                    return $query->whereIn('student_id', $studentIds);
                }
            }

            return $query->where('id', 0); // Return empty if no children
        }

        // Students can only see their own submissions
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                return $query->where('student_id', $student->id);
            }

            return $query->where('id', 0); // Return empty if student not found
        }

        // All other roles have no access
        return $query->where('id', 0);
    }
}
