<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherHomeworkResource\Pages;
use App\Models\Homework;
use App\Models\Subject;
use App\Models\Student;
use App\Models\SmsLog;
use App\Models\Employee;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TeacherHomeworkResource extends Resource
{
    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationLabel = 'Homework';

    protected static ?int $navigationSort = 2;

    // Only display records for the current teacher
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
        $teacherClasses = $teacher->classes()->get();
        $grades = $teacherClasses->pluck('grade')->unique()->toArray();

        // Get subjects assigned to this teacher
        $subjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

        // Filter homework by:
        // 1. Homework created by this teacher OR
        // 2. Homework for grades and subjects this teacher is assigned to
        return $query->where(function($query) use ($teacher, $grades, $subjectIds) {
            $query->where('assigned_by', $teacher->id)
                  ->orWhere(function($query) use ($grades, $subjectIds) {
                      $query->whereIn('grade', $grades)
                            ->whereIn('subject_id', $subjectIds);
                  });
        });
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = Employee::where('user_id', $user->id)->first();

        // Get only the subjects assigned to this teacher
        $subjectOptions = [];

        if ($teacher) {
            // Get the teacher's assigned subjects
            $teacherSubjects = $teacher->subjects()->pluck('name', 'id')->toArray();

            // If teacher has subjects assigned, use those
            if (!empty($teacherSubjects)) {
                $subjectOptions = $teacherSubjects;
            } else {
                // If no subjects assigned, get subjects based on teacher's department
                if (in_array($teacher->department, ['ECL', 'Primary'])) {
                    $subjectOptions = Subject::where('grade_level', $teacher->department)
                        ->orWhere('grade_level', 'All')
                        ->pluck('name', 'id')
                        ->toArray();
                } elseif ($teacher->department === 'Secondary') {
                    $subjectOptions = Subject::where('grade_level', 'Secondary')
                        ->orWhere('grade_level', 'All')
                        ->pluck('name', 'id')
                        ->toArray();
                } else {
                    // Fallback to all subjects
                    $subjectOptions = Subject::pluck('name', 'id')->toArray();
                }
            }

            // Log for debugging
            Log::info('Subject options for teacher', [
                'teacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'subject_count' => count($subjectOptions),
                'subjects' => array_keys($subjectOptions)
            ]);
        } else if ($user->hasRole('admin')) {
            $subjectOptions = Subject::pluck('name', 'id')->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Homework Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('homework_file')
                            ->label('Main Homework Document (PDF)')
                            ->helperText('Upload the main homework document that students will need to complete')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('homework-files')
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_attachment')
                            ->label('Additional Resources (Optional)')
                            ->helperText('Upload any additional resources or reference materials')
                            ->directory('homework-resources')
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Assignment Information')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options($subjectOptions)
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Components\Select $component, $state) use ($teacher) {
                                // If a subject is selected, we might want to filter grades based on it
                                if ($state) {
                                    $subject = Subject::find($state);
                                    if ($subject) {
                                        Log::info('Subject selected', [
                                            'subject_id' => $state,
                                            'subject_name' => $subject->name,
                                            'grade_level' => $subject->grade_level
                                        ]);
                                    }
                                }
                            }),
                        Forms\Components\Select::make('grade')
                            ->label('Grade')
                            ->options(function() use ($teacher, $user) {
                                if ($teacher) {
                                    return $teacher->classes()
                                        ->pluck('grade', 'grade')
                                        ->unique()
                                        ->toArray();
                                } else if ($user->hasRole('admin')) {
                                    return Student::select('grade')
                                        ->distinct()
                                        ->pluck('grade', 'grade')
                                        ->toArray();
                                }
                                return [];
                            })
                            ->required(),
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                            ])
                            ->required()
                            ->default('active'),
                        Forms\Components\Hidden::make('assigned_by')
                            ->default(function() use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),
                        Forms\Components\TextInput::make('max_score')
                            ->label('Maximum Score')
                            ->numeric()
                            ->default(100)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Submission Settings')
                    ->schema([
                        Forms\Components\DateTimePicker::make('submission_start')
                            ->label('Open Submission From')
                            ->helperText('When can students start submitting their homework?')
                            ->required(),
                        Forms\Components\DateTimePicker::make('submission_end')
                            ->label('Submission Deadline')
                            ->helperText('When is the regular submission deadline?')
                            ->required()
                            ->after('submission_start'),
                        Forms\Components\Toggle::make('allow_late_submission')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->reactive(),
                        Forms\Components\DateTimePicker::make('late_submission_deadline')
                            ->label('Late Submission Deadline')
                            ->helperText('Final deadline after which no submissions will be accepted')
                            ->required(fn (callable $get) => $get('allow_late_submission'))
                            ->visible(fn (callable $get) => $get('allow_late_submission'))
                            ->after('submission_end'),
                        Forms\Components\Textarea::make('submission_instructions')
                            ->label('Submission Instructions')
                            ->helperText('Provide detailed instructions on how to complete and submit the homework')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Parent Notification')
                    ->schema([
                        Forms\Components\Toggle::make('notify_parents')
                            ->label('Send SMS notifications to parents')
                            ->default(true)
                            ->helperText('This will automatically send SMS notifications to parents/guardians of students in this grade'),
                        Forms\Components\Textarea::make('sms_message')
                            ->label('Custom SMS Message (optional)')
                            ->placeholder('Leave empty to use the default message template')
                            ->helperText('Default template includes homework title, subject, and due date')
                            ->visible(fn (callable $get) => $get('notify_parents'))
                            ->maxLength(160),
                    ]),
            ]);
    }

    // ... [keep the rest of the class unchanged] ...

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->sortable()
                    ->label('Assigned By'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('submission_end')
                    ->dateTime()
                    ->label('Submission Deadline')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('submissions_count')
                    ->counts('submissions')
                    ->label('Submissions'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\Filter::make('due_date')
                    ->form([
                        Forms\Components\DatePicker::make('due_from'),
                        Forms\Components\DatePicker::make('due_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['due_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '>=', $date),
                            )
                            ->when(
                                $data['due_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('due_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('sendNotifications')
                    ->label('Send SMS Notifications')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Homework $record) {
                        HomeworkResource::sendSmsNotifications($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notifications')
                    ->modalDescription('This will send SMS notifications to all parents/guardians of students in this grade. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notifications')
                    ->visible(fn (Homework $record) => $record->status === 'active'),
                Tables\Actions\Action::make('viewSubmissions')
                    ->label('View Submissions')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->url(fn (Homework $record) => route('filament.admin.resources.teacher-homework-submissions.index', ['tableFilters[homework][value]' => $record->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->action(function (Builder $query) {
                            $query->update(['status' => 'completed']);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relation managers
            //Pages\RelationManagers\SubmissionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherHomework::route('/'),
            'create' => Pages\CreateTeacherHomework::route('/create'),
            //'view' => Pages\ViewTeacherHomework::route('/{record}'),
            'edit' => Pages\EditTeacherHomework::route('/{record}/edit'),
        ];
    }
}
