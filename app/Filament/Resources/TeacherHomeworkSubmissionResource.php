<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherHomeworkSubmissionResource\Pages;
use App\Models\HomeworkSubmission;
use App\Models\Homework;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Result;
use App\Constants\RoleConstants;
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

    // Display submissions based on user role
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Admin can see all submissions
        if ($user->role_id === RoleConstants::ADMIN) {
            return $query;
        }

        // Teachers can see submissions for their classes and homework
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return $query->where('id', 0); // Return empty result if not a teacher
            }

            // Get grades this teacher teaches
            $gradeIds = $teacher->classSections()->pluck('grade_id')->unique()->toArray();

            // Get subjects this teacher teaches
            $subjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

            // Get students in teacher's classes
            $studentIds = Student::whereIn('class_section_id',
                $teacher->classSections()->pluck('id')
            )->pluck('id')->toArray();

            // Get homework created by this teacher or for teacher's subjects/grades
            $homeworkIds = Homework::where(function($query) use ($teacher, $gradeIds, $subjectIds) {
                $query->where('assigned_by', $teacher->id)
                      ->orWhere(function($q) use ($gradeIds, $subjectIds) {
                          $q->whereIn('grade_id', $gradeIds)
                            ->whereIn('subject_id', $subjectIds);
                      });
            })->pluck('id')->toArray();

            // Return submissions from teacher's students for relevant homework
            return $query->where(function($query) use ($studentIds, $homeworkIds, $teacher) {
                $query->whereIn('student_id', $studentIds)
                      ->whereIn('homework_id', $homeworkIds)
                      ->orWhere('graded_by', $teacher->id);
            });
        }

        // Students can only see their own submissions
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            return $student ? $query->where('student_id', $student->id) : $query->where('id', 0);
        }

        // Parents can see submissions for their children
        if ($user->role_id === RoleConstants::PARENT) {
            $parent = $user->parentGuardian;
            $studentIds = $parent ? $parent->students()->pluck('id')->toArray() : [];
            return $query->whereIn('student_id', $studentIds);
        }

        return $query->where('id', 0); // Default: no access
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $isTeacher = $user->role_id === RoleConstants::TEACHER;
        $isAdmin = $user->role_id === RoleConstants::ADMIN;

        // Students and parents shouldn't create submissions through admin panel
        if ($user->role_id === RoleConstants::STUDENT || $user->role_id === RoleConstants::PARENT) {
            return $form->schema([
                Forms\Components\Placeholder::make('notice')
                    ->content('You can submit homework through the student portal.')
            ]);
        }

        $teacher = $isTeacher ? Teacher::where('user_id', $user->id)->first() : null;

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
                                        $set('graded_by', auth()->user()->teacher->id ?? null);
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
                            ->columnSpanFull()
                            ->hidden(fn () => $user->role_id !== RoleConstants::TEACHER && $user->role_id !== RoleConstants::ADMIN),
                        Forms\Components\Hidden::make('graded_by')
                            ->default(function() use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),
                        Forms\Components\DateTimePicker::make('graded_at')
                            ->default(now()),
                    ])->columns(2)
                    ->hidden(fn () => $user->role_id === RoleConstants::STUDENT || $user->role_id === RoleConstants::PARENT),

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
        $user = Auth::user();
        $canGrade = in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
        $isStudent = $user->role_id === RoleConstants::STUDENT;
        $isParent = $user->role_id === RoleConstants::PARENT;

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
                    ->sortable()
                    ->hidden($isStudent), // Hide for students since they only see their own
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
                        return "{$record->marks}/{$maxScore} (" . round(($record->marks / $maxScore) * 100) . "%)";
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_attachment')
                    ->label('Files')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_attachment)),
                Tables\Columns\TextColumn::make('feedback')
                    ->limit(50)
                    ->hidden(!$isStudent && !$isParent),
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
                Tables\Actions\EditAction::make()
                    ->visible($canGrade),
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
                        $teacher = Teacher::where('user_id', Auth::id())->first();

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
                    ->visible(fn ($record) => $canGrade && $record->status === 'submitted'),
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
                            $teacher = Teacher::where('user_id', Auth::id())->first();

                            $query->update([
                                'status' => 'graded',
                                'graded_by' => $teacher ? $teacher->id : null,
                                'graded_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Submissions marked as graded')
                                ->success()
                                ->send();
                        })
                        ->visible($canGrade),
                ])
                ->visible($canGrade),
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

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [
            RoleConstants::ADMIN,
            RoleConstants::TEACHER,
            RoleConstants::STUDENT,
            RoleConstants::PARENT
        ]) ?? false;
    }

    public static function canCreate(): bool
    {
        // Only teachers and admins can create submissions through admin panel
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]) ?? false;
    }

    public static function canEditAny(): bool
    {
        // Only teachers and admins can edit submissions
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        // Only admins can delete submissions
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }
}
