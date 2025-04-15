<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResultResource\Pages;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Homework;
use App\Models\SmsLog;
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

class TeacherResultResource extends Resource
{
    protected static ?string $model = Result::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Teaching';

    protected static ?string $navigationLabel = 'Results';

    protected static ?int $navigationSort = 4;

    // Only display results for the current teacher's classes and subjects
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
        $studentIds = Student::whereIn('class_id', $teacherClassIds)
            ->pluck('id')
            ->toArray();

        // Filter results by:
        // 1. Results recorded by this teacher OR
        // 2. Results for students in teacher's classes AND for subjects taught by this teacher
        return $query->where(function($query) use ($teacher, $studentIds, $teacherSubjectIds) {
            $query->where('recorded_by', $teacher->id)
                  ->orWhere(function($q) use ($studentIds, $teacherSubjectIds) {
                      $q->whereIn('student_id', $studentIds)
                        ->whereIn('subject_id', $teacherSubjectIds);
                  });
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
            $studentOptions = Student::whereIn('class_id', $teacherClassIds)
                ->pluck('name', 'id')
                ->toArray();
        } else if ($user->hasRole('admin')) {
            $studentOptions = Student::pluck('name', 'id')->toArray();
        }

        // Get subject options
        $subjectOptions = [];
        if ($teacher) {
            $subjectOptions = $teacher->subjects()->pluck('name', 'id')->toArray();
        } else if ($user->hasRole('admin')) {
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
                                if (!$student) {
                                    return [];
                                }

                                // If teacher is set, filter to their homework
                                $homeworkQuery = Homework::where('subject_id', $subjectId)
                                    ->where('grade', $student->grade);

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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->searchable()
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
                Tables\Actions\EditAction::make(),
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
                        ResultResource::sendResultNotification($record);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Send SMS Notification')
                    ->modalDescription('This will send an SMS notification to the student\'s parent/guardian about this result. Are you sure you want to continue?')
                    ->modalSubmitActionLabel('Yes, Send Notification'),
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
                                    ResultResource::sendResultNotification($result);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    $failCount++;
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
            'index' => Pages\ListTeacherResults::route('/'),
            'create' => Pages\CreateTeacherResult::route('/create'),
            //'view' => Pages\ViewTeacherResult::route('/{record}'),
            'edit' => Pages\EditTeacherResult::route('/{record}/edit'),
        ];
    }
}
