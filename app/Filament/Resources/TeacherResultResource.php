<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResultResource\Pages;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Homework;
use App\Models\SmsLog;
use App\Services\SmsService;
use App\Constants\RoleConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TeacherResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationLabel = 'Results';

    protected static ?int $navigationSort = 4;

    // Display results based on user role
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Admin can see all results
        if ($user->role_id === RoleConstants::ADMIN) {
            return $query;
        }

        // Teachers can see results for their students and subjects
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return $query->where('id', 0); // Return empty result if not a teacher
            }

            // Get class sections assigned to this teacher
            $classSectionIds = $teacher->classSections()->pluck('id')->toArray();

            // Get subjects assigned to this teacher
            $subjectIds = $teacher->subjects()->pluck('subjects.id')->toArray();

            // Get students in teacher's classes
            $studentIds = Student::whereIn('class_section_id', $classSectionIds)
                ->pluck('id')
                ->toArray();

            // Filter results by:
            // 1. Results recorded by this teacher OR
            // 2. Results for students in teacher's classes AND for subjects taught by this teacher
            return $query->where(function($query) use ($teacher, $studentIds, $subjectIds) {
                $query->where('recorded_by', $teacher->id)
                      ->orWhere(function($q) use ($studentIds, $subjectIds) {
                          $q->whereIn('student_id', $studentIds)
                            ->whereIn('subject_id', $subjectIds);
                      });
            });
        }

        // Students can only see their own results
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            return $student ? $query->where('student_id', $student->id) : $query->where('id', 0);
        }

        // Parents can see results for their children
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
        $isStudent = $user->role_id === RoleConstants::STUDENT;
        $isParent = $user->role_id === RoleConstants::PARENT;

        $teacher = $isTeacher ? Teacher::where('user_id', $user->id)->first() : null;

        // Students and parents shouldn't create results through admin panel
        if ($isStudent || $isParent) {
            return $form->schema([
                Forms\Components\Placeholder::make('notice')
                    ->content('Results can only be created by teachers.')
            ]);
        }

        // Get teacher's class sections
        $classSectionIds = [];
        if ($teacher) {
            $classSectionIds = $teacher->classSections()->pluck('id')->toArray();
        }

        // Get students in teacher's classes
        $studentOptions = [];
        if (!empty($classSectionIds)) {
            $studentOptions = Student::whereIn('class_section_id', $classSectionIds)
                ->get()
                ->mapWithKeys(function ($student) {
                    $grade = $student->grade ? $student->grade->name : 'Unknown';
                    return [$student->id => "{$student->name} ({$grade})"];
                })
                ->toArray();
        } else if ($isAdmin) {
            $studentOptions = Student::with('grade')
                ->get()
                ->mapWithKeys(function ($student) {
                    $grade = $student->grade ? $student->grade->name : 'Unknown';
                    return [$student->id => "{$student->name} ({$grade})"];
                })
                ->toArray();
        }

        // Get subject options
        $subjectOptions = [];
        if ($teacher) {
            $subjectOptions = $teacher->subjects()->pluck('name', 'id')->toArray();
        } else if ($isAdmin) {
            $subjectOptions = Subject::pluck('name', 'id')->toArray();
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Student & Subject')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->options($studentOptions)
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->options($subjectOptions)
                            ->required()
                            ->reactive(),
                    ])->columns(2),

                Forms\Components\Section::make('Exam Information')
                    ->schema([
                        Forms\Components\Select::make('exam_type')
                            ->options([
                                'mid-term' => 'Mid-Term',
                                'final' => 'Final',
                                'quiz' => 'Quiz',
                                'assignment' => 'Assignment',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('homework_id')
                            ->label('Linked Homework')
                            ->options(function (callable $get) use ($teacher) {
                                $studentId = $get('student_id');
                                $subjectId = $get('subject_id');

                                if (!$studentId || !$subjectId) {
                                    return [];
                                }

                                // Get student grade
                                $student = Student::find($studentId);
                                if (!$student || !$student->grade_id) {
                                    return [];
                                }

                                // If teacher is set, filter to their homework
                                $homeworkQuery = Homework::where('subject_id', $subjectId)
                                    ->where('grade_id', $student->grade_id);

                                if ($teacher) {
                                    $homeworkQuery->where(function($query) use ($teacher) {
                                        $query->where('assigned_by', $teacher->id)
                                              ->orWhereIn('subject_id', $teacher->subjects()->pluck('subjects.id'));
                                    });
                                }

                                return $homeworkQuery->orderBy('title')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => $get('exam_type') === 'assignment')
                            ->required(fn (callable $get) => $get('exam_type') === 'assignment')
                            ->helperText('Select the homework assignment this result is based on'),

                        Forms\Components\TextInput::make('marks')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('grade')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('term')
                            ->options([
                                'first' => 'First Term',
                                'second' => 'Second Term',
                                'third' => 'Third Term',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->default(date('Y')),
                    ])->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('recorded_by')
                            ->default(function() use ($teacher) {
                                return $teacher ? $teacher->id : null;
                            }),

                        Forms\Components\Toggle::make('notify_parent')
                            ->label('Send SMS notification to parent')
                            ->default(true)
                            ->reactive(),

                        Forms\Components\Textarea::make('sms_message')
                            ->label('Custom SMS Message (optional)')
                            ->placeholder('Leave empty to use the default message template')
                            ->helperText('Default template includes student name, subject, and result details')
                            ->visible(fn (callable $get) => $get('notify_parent'))
                            ->maxLength(160),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();
        $canEdit = in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]);
        $isStudent = $user->role_id === RoleConstants::STUDENT;
        $isParent = $user->role_id === RoleConstants::PARENT;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
                    ->sortable()
                    ->hidden($isStudent), // Hide for students since they only see their own
                Tables\Columns\TextColumn::make('student.grade.name')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('exam_type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('homework.title')
                    ->label('Homework')
                    ->visible(fn ($record) => $record && $record->exam_type === 'assignment' && $record->homework_id)
                    ->searchable(),
                Tables\Columns\TextColumn::make('marks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('term')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('comment')
                    ->limit(50)
                    ->visible($isStudent || $isParent),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),
                Tables\Filters\SelectFilter::make('exam_type')
                    ->options([
                        'mid-term' => 'Mid-Term',
                        'final' => 'Final',
                        'quiz' => 'Quiz',
                        'assignment' => 'Assignment',
                    ]),
                Tables\Filters\SelectFilter::make('term')
                    ->options([
                        'first' => 'First Term',
                        'second' => 'Second Term',
                        'third' => 'Third Term',
                    ]),
                Tables\Filters\Filter::make('year')
                    ->form([
                        Forms\Components\TextInput::make('year'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when($data['year'], fn($q) => $q->where('year', $data['year']))
                    ),
                Tables\Filters\Filter::make('homework')
                    ->label('Has Linked Homework')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('homework_id'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible($canEdit),
                Tables\Actions\Action::make('viewHomework')
                    ->label('View Homework')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->url(fn (Result $record) => $record && $record->homework_id
                        ? route('filament.admin.resources.teacher-homework.view', ['record' => $record->homework_id])
                        : null)
                    ->visible(fn (Result $record) => $record && $record->exam_type === 'assignment' && $record->homework_id)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('sendNotification')
                    ->label('Send SMS Notification')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->action(function (Result $record) {
                        self::sendResultNotification($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notification')
                    ->modalDescription('This will send an SMS notification to the student\'s parent/guardian about this result. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notification')
                    ->visible($canEdit),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_notifications')
                        ->label('Send SMS Notifications')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->action(function (Builder $query) {
                            $results = $query->get();
                            $successCount = 0;
                            $failCount = 0;

                            foreach ($results as $result) {
                                try {
                                    self::sendResultNotification($result);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    $failCount++;
                                    Log::error('Failed to send result notification', [
                                        'result_id' => $result->id,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }

                            Notification::make()
                                ->title('Notifications Sent')
                                ->body("Successfully sent: {$successCount}, Failed: {$failCount}")
                                ->success($successCount > 0)
                                ->warning($failCount > 0)
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Send Bulk SMS Notifications')
                        ->modalDescription('This will send SMS notifications for all selected results. Are you sure you want to continue?')
                        ->modalSubmitActionLabel('Yes, Send All Notifications'),
                ])
                ->visible($canEdit),
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
            'index' => Pages\ListTeacherResults::route('/'),
            'create' => Pages\CreateTeacherResult::route('/create'),
            //'view' => Pages\ViewTeacherResult::route('/{record}'),
            'edit' => Pages\EditTeacherResult::route('/{record}/edit'),
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
        // Only teachers and admins can create results
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]) ?? false;
    }

    public static function canEditAny(): bool
    {
        // Only teachers and admins can edit results
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER]) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        // Only admins can delete results
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    /**
     * Send SMS notification for a result
     */
    public static function sendResultNotification(Result $result): bool
    {
        try {
            // Get student and parent information
            $student = $result->student;
            if (!$student) {
                throw new \Exception('Student not found');
            }

            $parent = $student->parentGuardian;
            if (!$parent || !$parent->phone) {
                throw new \Exception('Parent or phone number not found');
            }

            // Prepare message
            $message = $result->sms_message ?? "Your child {$student->name} scored {$result->marks}% ({$result->grade}) in {$result->subject->name} ({$result->exam_type}). Great work!";

            // Send SMS (you'll need to implement this based on your SMS service)
            // $smsService = app(SmsService::class);
            // $smsService->send($parent->phone, $message);

            // Log SMS
            SmsLog::create([
                'recipient' => $parent->phone,
                'message' => $message,
                'status' => 'sent',
                'cost' => 0.50, // Adjust based on your SMS pricing
                'reference_id' => $result->id,
                'message_type' => 'result_notification',
            ]);

            Notification::make()
                ->title('SMS Sent Successfully')
                ->body("Notification sent to {$parent->phone}")
                ->success()
                ->send();

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('SMS Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();

            Log::error('Failed to send result SMS', [
                'result_id' => $result->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
