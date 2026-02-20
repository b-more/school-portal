<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\HomeworkResource\Pages;
use App\Models\Grade;
use App\Models\Homework;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Traits\HasTeacherAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HomeworkResource extends Resource
{
    use HasTeacherAccess;

    protected static ?string $model = Homework::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationLabel = 'Homework';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER, RoleConstants::PARENT, RoleConstants::STUDENT, RoleConstants::SCHOOL_SECRETARY]);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();

        if ($user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            return $teacher && $record->assigned_by === $teacher->id;
        }

        return false;
    }

    public static function canDelete($record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        $teacher = null;

        if ($user && $user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
        }

        // Get subjects for the teacher or all subjects for admin
        $subjectOptions = [];
        if ($teacher) {
            $subjectOptions = $teacher->subjects()->pluck('name', 'subjects.id')->toArray();
        } elseif ($user && $user->role_id === RoleConstants::ADMIN) {
            $subjectOptions = Subject::where('is_active', true)->pluck('name', 'id')->toArray();
        }

        // Get grades for the teacher or all grades for admin
        $gradeOptions = [];
        if ($teacher) {
            // Get grades from teacher's assigned class sections
            $gradeIds = $teacher->classSections()
                ->with('grade')
                ->get()
                ->pluck('grade.id')
                ->unique()
                ->filter();

            $gradeOptions = Grade::whereIn('id', $gradeIds)
                ->pluck('name', 'id')
                ->toArray();
        } elseif ($user && $user->role_id === RoleConstants::ADMIN) {
            $gradeOptions = Grade::where('is_active', true)->pluck('name', 'id')->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Homework Information')
                    ->description('Create homework assignment for students')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Enter homework title'),

                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options($subjectOptions)
                            ->searchable()
                            ->required()
                            ->helperText($teacher ? 'You can only assign homework for subjects you teach' : 'Select a subject')
                            ->placeholder('Select subject'),

                        Forms\Components\Select::make('grade_id')
                            ->label('Grade')
                            ->options($gradeOptions)
                            ->required()
                            ->helperText($teacher ? 'You can only assign homework to grades you teach' : 'Select a grade')
                            ->placeholder('Select grade'),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Due Date & Time')
                            ->required()
                            ->default(now()->addWeek())
                            ->minDate(now())
                            ->helperText('Set when this homework is due'),

                        Forms\Components\Textarea::make('description')
                            ->label('Instructions')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Provide detailed instructions for students')
                            ->helperText('Clear instructions help students complete the homework successfully'),

                        Forms\Components\FileUpload::make('homework_file')
                            ->label('Homework Document')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'])
                            ->directory('homework-files')
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Upload PDF, Word document, or image file (Max: 10MB)')
                            ->downloadable()
                            ->openable()
                            ->previewable(),

                        Forms\Components\TextInput::make('max_score')
                            ->label('Maximum Score')
                            ->numeric()
                            ->default(100)
                            ->required()
                            ->minValue(1)
                            ->helperText('Total marks for this homework'),

                        Forms\Components\Toggle::make('allow_late_submission')
                            ->label('Allow Late Submissions')
                            ->default(false)
                            ->live()
                            ->helperText('Allow students to submit after due date'),

                        Forms\Components\DateTimePicker::make('late_submission_deadline')
                            ->label('Late Submission Deadline')
                            ->visible(fn (Forms\Get $get) => $get('allow_late_submission'))
                            ->minDate(fn (Forms\Get $get) => $get('due_date'))
                            ->helperText('Final deadline for late submissions'),

                        Forms\Components\Hidden::make('assigned_by')
                            ->default(function () use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),

                        Forms\Components\Hidden::make('status')
                            ->default('active'),

                        Forms\Components\Toggle::make('notify_parents')
                            ->label('Send SMS notifications to parents')
                            ->default(true)
                            ->helperText('Automatically notify all parents of students in this grade')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = Auth::user();

                // Filter based on user role
                if ($user && $user->role_id === RoleConstants::TEACHER) {
                    $teacher = Teacher::where('user_id', $user->id)->first();
                    if ($teacher) {
                        // Show homework assigned by this teacher or for grades they teach
                        $query->where(function ($q) use ($teacher) {
                            $q->where('assigned_by', $teacher->id)
                                ->orWhereHas('grade', function ($gradeQuery) use ($teacher) {
                                    $gradeQuery->whereHas('classSections', function ($classQuery) use ($teacher) {
                                        $classQuery->whereHas('subjectTeachings', function ($teachingQuery) use ($teacher) {
                                            $teachingQuery->where('teacher_id', $teacher->id);
                                        });
                                    });
                                });
                        });
                    }
                } elseif ($user && $user->role_id === RoleConstants::PARENT) {
                    // Parents can only see homework for their children's grades
                    $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();
                    if ($parentGuardian) {
                        $childrenGradeIds = $parentGuardian->students()
                            ->where('enrollment_status', 'active')
                            ->pluck('grade_id')
                            ->unique();

                        $query->whereIn('grade_id', $childrenGradeIds);
                    }
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),

                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->sortable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->sortable()
                    ->label('Teacher')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->due_date->isPast()) {
                            return 'danger';
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            return 'warning';
                        }

                        return 'success';
                    })
                    ->icon(function ($record) {
                        if ($record->due_date->isPast()) {
                            return 'heroicon-o-exclamation-triangle';
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            return 'heroicon-o-clock';
                        }

                        return 'heroicon-o-calendar';
                    }),

                Tables\Columns\TextColumn::make('max_score')
                    ->label('Max Score')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('homework_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->homework_file))
                    ->icon('heroicon-o-document')
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                        'danger' => 'overdue',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'active',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'overdue',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name')
                    ->options(function () {
                        $user = Auth::user();
                        if ($user && $user->role_id === RoleConstants::TEACHER) {
                            $teacher = Teacher::where('user_id', $user->id)->first();
                            if ($teacher) {
                                return $teacher->subjects()->pluck('name', 'subjects.id')->toArray();
                            }
                        }

                        return Subject::where('is_active', true)->pluck('name', 'id')->toArray();
                    }),

                Tables\Filters\SelectFilter::make('grade')
                    ->relationship('grade', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                    ]),

                Tables\Filters\Filter::make('due_soon')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<=', now()->addDays(3)))
                    ->label('Due Within 3 Days')
                    ->toggle(),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now()))
                    ->label('Overdue')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (Homework $record) {
                        if (! $record->homework_file) {
                            Notification::make()
                                ->title('No file available')
                                ->warning()
                                ->send();

                            return;
                        }

                        $filePath = storage_path('app/public/'.$record->homework_file);

                        if (! file_exists($filePath)) {
                            Notification::make()
                                ->title('File not found')
                                ->body('The homework file could not be found on the server.')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Get file extension and name
                        $fileName = $record->title.'_'.$record->subject->name.'_'.$record->grade->name;
                        $extension = pathinfo($record->homework_file, PATHINFO_EXTENSION);
                        $downloadName = $fileName.'.'.$extension;

                        return response()->download($filePath, $downloadName);
                    })
                    ->visible(fn (Homework $record) => ! empty($record->homework_file)),

                Tables\Actions\Action::make('view_file')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(function (Homework $record) {
                        if (! $record->homework_file) {
                            return null;
                        }

                        return Storage::url($record->homework_file);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (Homework $record) => ! empty($record->homework_file)),

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Homework $record) => static::canEdit($record)),

                Tables\Actions\Action::make('sendNotifications')
                    ->label('Send SMS')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Homework $record) {
                        static::sendSmsNotifications($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notifications')
                    ->modalDescription('This will send SMS notifications to all parents/guardians of students in this grade. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notifications')
                    ->visible(function (Homework $record) {
                        $user = Auth::user();

                        return $user && in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Homework $record) => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function () {
                            $user = Auth::user();

                            return $user && in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Send SMS notifications to parents about new homework
     */
    public static function sendSmsNotifications(Homework $homework): void
    {
        // Get all students in the specified grade
        $students = Student::where('grade_id', $homework->grade_id)
            ->where('enrollment_status', 'active')
            ->with('parentGuardian')
            ->get();

        if ($students->isEmpty()) {
            Notification::make()
                ->title('No students found')
                ->body('No active students found in this grade.')
                ->warning()
                ->send();

            return;
        }

        $successCount = 0;
        $failCount = 0;
        $noPhoneCount = 0;

        foreach ($students as $student) {
            $parentGuardian = $student->parentGuardian;

            if (! $parentGuardian || ! $parentGuardian->phone) {
                $noPhoneCount++;

                continue;
            }

            try {
                // Construct the SMS message - MUST BE UNDER 159 CHARACTERS
                $subjectName = $homework->subject->name ?? 'Unknown Subject';
                $dueDate = $homework->due_date->format('d/m/Y');

                // Short message format to stay under 159 characters
                $message = "New homework: {$subjectName}\n";
                $message .= "Student: {$student->name}\n";
                $message .= "Due: {$dueDate}\n";
                $message .= "Check portal for details.\n";
                $message .= "St Francis of Assisi";

                // Ensure message is under 159 characters
                if (strlen($message) > 159) {
                    // Truncate if needed
                    $message = substr($message, 0, 156) . '...';
                }

                // Format and send SMS using SmsService
                $smsService = app(\App\Services\SmsService::class);
                $success = $smsService->send(
                    $message,
                    $parentGuardian->phone,
                    'homework_notification',
                    $homework->id
                );

                // Also send email notification
                if ($parentGuardian->email) {
                    try {
                        static::sendEmailNotification($homework, $student, $parentGuardian);
                    } catch (\Exception $emailError) {
                        Log::error('Failed to send homework email', [
                            'homework_id' => $homework->id,
                            'parent_email' => $parentGuardian->email,
                            'error' => $emailError->getMessage(),
                        ]);
                    }
                }

                if ($success) {
                    $successCount++;

                    // Log successful SMS
                    Log::info('Homework SMS sent successfully', [
                        'homework_id' => $homework->id,
                        'homework_title' => $homework->title,
                        'student_id' => $student->id,
                        'student_name' => $student->name,
                        'parent_guardian_id' => $parentGuardian->id,
                        'parent_name' => $parentGuardian->name,
                        'phone' => substr($formattedPhone, 0, 6).'****'.substr($formattedPhone, -3),
                    ]);
                } else {
                    $failCount++;
                }

            } catch (\Exception $e) {
                $failCount++;

                // Log error
                Log::error('Failed to send homework SMS', [
                    'homework_id' => $homework->id,
                    'homework_title' => $homework->title,
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'parent_guardian_id' => $parentGuardian->id,
                    'parent_name' => $parentGuardian->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Show comprehensive notification with results
        $message = "SMS Notification Results:\n";
        $message .= "✅ Successfully sent: {$successCount}\n";
        $message .= "❌ Failed to send: {$failCount}\n";
        $message .= "📱 No phone number: {$noPhoneCount}\n";
        $message .= '👥 Total students: '.$students->count();

        Notification::make()
            ->title('SMS Notifications Complete')
            ->body($message)
            ->success($successCount > 0)
            ->warning($failCount > 0 || $noPhoneCount > 0)
            ->duration(8000)
            ->send();
    }

    /**
     * Send email notification to parent about new homework
     */
    public static function sendEmailNotification(Homework $homework, Student $student, ParentGuardian $parent): void
    {
        try {
            $subjectName = $homework->subject->name ?? 'Unknown Subject';
            $gradeName = $homework->grade->name ?? 'Unknown Grade';
            $teacherName = $homework->assignedBy->name ?? 'Teacher';
            $dueDate = $homework->due_date->format('l, F j, Y \a\t g:i A');

            $emailData = [
                'parent_name' => $parent->name,
                'student_name' => $student->name,
                'subject_name' => $subjectName,
                'homework_title' => $homework->title,
                'homework_description' => $homework->description,
                'grade_name' => $gradeName,
                'teacher_name' => $teacherName,
                'due_date' => $dueDate,
                'max_score' => $homework->max_score,
            ];

            \Mail::to($parent->email)->send(new \App\Mail\HomeworkNotification($emailData));

            Log::info('Homework email sent successfully', [
                'homework_id' => $homework->id,
                'parent_email' => $parent->email,
                'student_name' => $student->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send homework email notification', [
                'homework_id' => $homework->id,
                'parent_email' => $parent->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'subject:id,name',
                'grade:id,name',
                'assignedBy:id,name',
            ])
            ->withCount('submissions');

        $user = Auth::user();

        // Admin and Secretary see all homework
        if ($user && $user->role_id === RoleConstants::SCHOOL_SECRETARY) {
            return $query;
        }

        // Apply role-based filtering
        if ($user && $user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                return static::filterHomeworkForTeacher($query, $teacher);
            }
        } elseif ($user && $user->role_id === RoleConstants::PARENT) {
            return static::filterHomeworkForParent($query, $user);
        } elseif ($user && $user->role_id === RoleConstants::STUDENT) {
            return static::filterHomeworkForStudent($query, $user);
        }

        return $query;
    }

    /**
     * Filter homework query for teacher access
     */
    protected static function filterHomeworkForTeacher(Builder $query, Teacher $teacher): Builder
    {
        return $query->where(function ($q) use ($teacher) {
            // Homework assigned by this teacher
            $q->where('assigned_by', $teacher->id)
              // Or homework for grades where teacher has class sections
                ->orWhereHas('grade', function ($gradeQuery) use ($teacher) {
                    $gradeQuery->whereHas('classSections', function ($classQuery) use ($teacher) {
                        $classQuery->whereHas('subjectTeachings', function ($teachingQuery) use ($teacher) {
                            $teachingQuery->where('teacher_id', $teacher->id);
                        });
                    });
                });
        });
    }

    /**
     * Filter homework query for parent access
     */
    protected static function filterHomeworkForParent(Builder $query, $user): Builder
    {
        $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

        if (! $parentGuardian) {
            return $query->whereRaw('1 = 0'); // Return no results if parent not found
        }

        // Get children's grade IDs
        $childrenGradeIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('grade_id')
            ->unique();

        return $query->whereIn('grade_id', $childrenGradeIds);
    }

    /**
     * Filter homework query for student access
     */
    protected static function filterHomeworkForStudent(Builder $query, $user): Builder
    {
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return $query->whereRaw('1 = 0'); // Return no results if student not found
        }

        // Show only active homework for student's grade
        return $query->where('grade_id', $student->grade_id)
            ->where('status', 'active');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHomework::route('/'),
            'create' => Pages\CreateHomework::route('/create'),
            'view' => Pages\ViewHomework::route('/{record}'),
            'edit' => Pages\EditHomework::route('/{record}/edit'),
        ];
    }
}
