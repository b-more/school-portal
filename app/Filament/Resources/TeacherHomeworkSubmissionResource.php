<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherHomeworkSubmissionResource\Pages;
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
use Illuminate\Support\Facades\Auth;

class TeacherHomeworkSubmissionResource extends Resource
{
    protected static ?string $model = HomeworkSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationLabel = 'Homework Submissions';

    protected static ?int $navigationSort = 3;

    // Only display submissions for the current teacher's classes and subjects
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Get the current user
        $user = Auth::user();

        // If user is admin, show all records
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Get the employee (teacher) record
        $teacher = Employee::where('user_id', $user->id)->first();

        if (!$teacher) {
            return $query->where('id', 0); // Return empty result if not a teacher
        }

        // Get classes assigned to this teacher
        $teacherClassIds = $teacher->classes()->pluck('id')->toArray();

        // Get subjects assigned to this teacher
        $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

        // Get students in teacher's classes
        $studentIds = Student::whereIn('school_class_id', $teacherClassIds)
            ->pluck('id')
            ->toArray();

        // Get homework created by this teacher
        $teacherHomeworkIds = Homework::where('assigned_by', $teacher->id)
            ->pluck('id')
            ->toArray();

        // Get homework for teacher's subjects and grades
        $classGrades = $teacher->classes()->pluck('grade')->unique()->toArray();
        $subjectHomeworkIds = Homework::whereIn('subject_id', $teacherSubjectIds)
            ->whereIn('grade', $classGrades)
            ->pluck('id')
            ->toArray();

        $relevantHomeworkIds = array_unique(array_merge($teacherHomeworkIds, $subjectHomeworkIds));

        // Filter submissions by:
        // 1. Submissions from students in teacher's classes AND for teacher's subjects
        // 2. OR submissions for homework created by this teacher
        return $query->where(function($query) use ($studentIds, $relevantHomeworkIds, $teacher) {
            $query->whereIn('student_id', $studentIds)
                  ->whereIn('homework_id', $relevantHomeworkIds)
                  ->orWhereIn('homework_id', $relevantHomeworkIds)
                  ->orWhere('graded_by', $teacher->id);
        });
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = Employee::where('user_id', $user->id)->first();

        // Get teacher's classes
        $teacherClassIds = [];
        if ($teacher) {
            $teacherClassIds = $teacher->classes()->pluck('id')->toArray();
        }

        // Get students in teacher's classes
        $studentOptions = [];
        if (!empty($teacherClassIds)) {
            $studentOptions = Student::whereIn('school_class_id', $teacherClassIds)
                ->pluck('name', 'id')
                ->toArray();
        } else if ($user->hasRole('admin')) {
            $studentOptions = Student::pluck('name', 'id')->toArray();
        }

        // Get homework options
        $homeworkOptions = [];
        if ($teacher) {
            // Get subjects assigned to this teacher
            $teacherSubjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

            // Get grades teacher is teaching
            $classGrades = $teacher->classes()->pluck('grade')->unique()->toArray();

            $homeworkOptions = Homework::where(function($query) use ($teacher, $teacherSubjectIds, $classGrades) {
                    $query->where('assigned_by', $teacher->id)
                          ->orWhere(function($q) use ($teacherSubjectIds, $classGrades) {
                              $q->whereIn('subject_id', $teacherSubjectIds)
                                ->whereIn('grade', $classGrades);
                          });
                })
                ->pluck('title', 'id')
                ->toArray();
        } else if ($user->hasRole('admin')) {
            $homeworkOptions = Homework::pluck('title', 'id')->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        Forms\Components\Select::make('homework_id')
                            ->relationship('homework', 'title')
                            ->searchable()
                            ->preload()
                            ->options($homeworkOptions)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('marks', null)),
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->options($studentOptions)
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
                                        $set('graded_by', auth()->user()->employee->id ?? null);
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
                        Forms\Components\Hidden::make('graded_by')
                            ->default(function() use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('homework')
                    ->relationship('homework', 'title'),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
                        $teacher = Employee::where('user_id', Auth::id())->first();

                        $record->update([
                            'marks' => $data['marks'],
                            'feedback' => $data['feedback'],
                            'teacher_notes' => $data['teacher_notes'] ?? null,
                            'status' => 'graded',
                            'graded_by' => $teacher ? $teacher->id : null,
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
                        $teacher = Employee::where('user_id', Auth::id())->first();

                        if (!$homework || !$student) {
                            Notification::make()
                                ->title('Missing Information')
                                ->body('Cannot create result due to missing homework or student information.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Determine letter grade from marks
                        if ($record->marks >= 90) $grade = 'A+';
                        elseif ($record->marks >= 80) $grade = 'A';
                        elseif ($record->marks >= 70) $grade = 'B';
                        elseif ($record->marks >= 60) $grade = 'C';
                        elseif ($record->marks >= 50) $grade = 'D';
                        else $grade = 'F';

                        // Create corresponding result record
                        $result = Result::create([
                            'student_id' => $student->id,
                            'subject_id' => $homework->subject_id,
                            'exam_type' => 'assignment',
                            'homework_id' => $homework->id,
                            'marks' => $record->marks,
                            'grade' => $grade,
                            'term' => 'first', // Default - you may want to set this dynamically
                            'year' => date('Y'),
                            'comment' => $record->feedback,
                            'recorded_by' => $teacher ? $teacher->id : ($record->graded_by ?? null),
                            'notify_parent' => true,
                        ]);

                        // Show success notification
                        Notification::make()
                            ->title('Result Created')
                            ->body('Result record has been created successfully.')
                            ->success()
                            ->send();

                        // Redirect to the result edit page
                        redirect()->route('filament.admin.resources.teacher-results.edit', ['record' => $result->id]);
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

                        redirect()->route('filament.admin.resources.teacher-results.view', ['record' => $result->id]);
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
                    ->url(fn ($record) => route('filament.resources.teacher-homework-submissions.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->file_attachment)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_grade')
                        ->label('Bulk Mark as Graded')
                        ->icon('heroicon-o-check')
                        ->action(function (Builder $query) {
                            $teacher = Employee::where('user_id', Auth::id())->first();

                            // Update all selected records to graded status
                            $query->update([
                                'status' => 'graded',
                                'graded_by' => $teacher ? $teacher->id : null,
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
                            $teacher = Employee::where('user_id', Auth::id())->first();

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
                                    // Determine letter grade from marks
                                    if ($submission->marks >= 90) $grade = 'A+';
                                    elseif ($submission->marks >= 80) $grade = 'A';
                                    elseif ($submission->marks >= 70) $grade = 'B';
                                    elseif ($submission->marks >= 60) $grade = 'C';
                                    elseif ($submission->marks >= 50) $grade = 'D';
                                    else $grade = 'F';

                                    // Create corresponding result record
                                    Result::create([
                                        'student_id' => $student->id,
                                        'subject_id' => $homework->subject_id,
                                        'exam_type' => 'assignment',
                                        'homework_id' => $homework->id,
                                        'marks' => $submission->marks,
                                        'grade' => $grade,
                                        'term' => 'first', // Default
                                        'year' => date('Y'),
                                        'comment' => $submission->feedback,
                                        'recorded_by' => $teacher ? $teacher->id : ($submission->graded_by ?? null),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherHomeworkSubmissions::route('/'),
            'create' => Pages\CreateTeacherHomeworkSubmission::route('/create'),
            //'view' => Pages\ViewTeacherHomeworkSubmission::route('/{record}'),
            'edit' => Pages\EditTeacherHomeworkSubmission::route('/{record}/edit'),
        ];
    }
}
