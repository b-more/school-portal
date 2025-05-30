<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Teachers';
    protected static ?string $navigationGroup = 'Staff Management';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canEditAny(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Personal Information')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('employee_id')
                                    ->label('Employee ID')
                                    ->unique(table: Teacher::class, column: 'employee_id', ignoreRecord: true)
                                    ->maxLength(50)
                                    ->default(fn () => static::generateEmployeeId())
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('qualification')
                                    ->options([
                                        'Certificate' => 'Certificate',
                                        'Diploma' => 'Diploma',
                                        'Advanced Diploma' => 'Advanced Diploma',
                                        'Degree' => 'Degree',
                                        'Masters' => 'Masters',
                                        'PhD' => 'PhD',
                                    ])
                                    ->required(),
                                Forms\Components\DatePicker::make('join_date')
                                    ->label('Date Joined')
                                    ->default(now()),
                                Forms\Components\FileUpload::make('profile_photo')
                                    ->label('Profile Photo')
                                    ->image()
                                    ->directory('teacher-photos')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('1:1')
                                    ->imageResizeTargetWidth('300')
                                    ->imageResizeTargetHeight('300'),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('address')
                                    ->rows(3)
                                    ->maxLength(500),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Teacher Classification')
                            ->schema([
                                Forms\Components\Select::make('teacher_type')
                                    ->label('Teacher Type')
                                    ->options([
                                        'primary' => 'Primary School Teacher (Baby Class to Grade 7)',
                                        'secondary' => 'Secondary School Teacher (Grades 8-12)',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, string $state) {
                                        if ($state === 'primary') {
                                            $set('is_grade_teacher', true);
                                            $set('is_class_teacher', true);
                                            $set('specialization', null); // Clear specialization for primary teachers
                                        } else {
                                            $set('is_class_teacher', false);
                                            $set('grade_id', null); // Clear grade for secondary teachers initially
                                            $set('class_section_id', null);
                                        }
                                    })
                                    ->columnSpanFull(),

                                // For Primary Teachers (Baby Class to Grade 7)
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('primary_teacher_info')
                                            ->content('Primary teachers are automatically assigned to ALL subjects for their assigned grade and class section. They will teach all subjects to one class.')
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('grade_id')
                                            ->label('Assigned Grade')
                                            ->options(function () {
                                                return Grade::whereIn('name', [
                                                    'Baby Class', 'Middle Class', 'Reception',
                                                    'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                                                    'Grade 5', 'Grade 6', 'Grade 7'
                                                ])
                                                ->orderBy('level')
                                                ->pluck('name', 'id');
                                            })
                                            ->required()
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                                $set('class_section_id', null);

                                                // Show subjects that will be automatically assigned
                                                if ($state) {
                                                    $grade = Grade::with('subjects')->find($state);
                                                    if ($grade && $grade->subjects->count() > 0) {
                                                        $subjectNames = $grade->subjects->pluck('name')->join(', ');
                                                        $set('auto_assigned_subjects', $subjectNames);
                                                    } else {
                                                        $set('auto_assigned_subjects', 'No subjects found for this grade. Please assign subjects to this grade first.');
                                                    }
                                                } else {
                                                    $set('auto_assigned_subjects', '');
                                                }
                                            })
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'primary';
                                            }),

                                        Forms\Components\Placeholder::make('auto_assigned_subjects')
                                            ->label('Subjects that will be automatically assigned')
                                            ->content(function (Forms\Get $get) {
                                                return $get('auto_assigned_subjects') ?: 'Select a grade to see subjects';
                                            })
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'primary' && $get('grade_id');
                                            }),

                                        Forms\Components\Select::make('class_section_id')
                                            ->label('Assigned Class Section')
                                            ->options(function (Forms\Get $get) {
                                                $gradeId = $get('grade_id');
                                                if (!$gradeId) {
                                                    return [];
                                                }

                                                return ClassSection::where('grade_id', $gradeId)
                                                    ->where('is_active', true)
                                                    ->with('grade')
                                                    ->get()
                                                    ->mapWithKeys(function ($section) {
                                                        $studentCount = $section->students()->where('enrollment_status', 'active')->count();
                                                        return [$section->id => "{$section->grade->name} - {$section->name} ({$studentCount} students)"];
                                                    });
                                            })
                                            ->preload(false)
                                            ->searchable()
                                            ->required()
                                            ->helperText('The teacher will be assigned as the class teacher for this section.')
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'primary' && $get('grade_id');
                                            }),
                                    ])
                                    ->visible(function (Forms\Get $get) {
                                        return $get('teacher_type') === 'primary';
                                    })
                                    ->columns(2),

                                // For Secondary Teachers (Grades 8-12)
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('secondary_teacher_info')
                                            ->content('Secondary teachers specialize in specific subjects. Optionally assign a grade if they are a grade teacher.')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('specialization')
                                            ->label('Subject Specialization')
                                            ->maxLength(255)
                                            ->required()
                                            ->helperText('e.g., Mathematics, Physics, English Literature')
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary';
                                            }),

                                        Forms\Components\Toggle::make('is_grade_teacher')
                                            ->label('Is Grade Teacher?')
                                            ->helperText('Grade teachers have additional responsibilities for overseeing an entire grade level')
                                            ->default(false)
                                            ->live()
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary';
                                            }),

                                        Forms\Components\Select::make('grade_id')
                                            ->label('Responsible Grade')
                                            ->options(function () {
                                                return Grade::whereIn('name', [
                                                    'Grade 8', 'Grade 9', 'Grade 10',
                                                    'Grade 11', 'Grade 12'
                                                ])
                                                ->orderBy('level')
                                                ->pluck('name', 'id');
                                            })
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary' && $get('is_grade_teacher') === true;
                                            })
                                            ->required(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary' && $get('is_grade_teacher') === true;
                                            }),

                                        Forms\Components\Repeater::make('subject_classes')
                                            ->label('Subject and Class Assignments')
                                            ->schema([
                                                Forms\Components\Select::make('subject_id')
                                                    ->label('Subject')
                                                    ->options(function () {
                                                        return Subject::where('is_active', true)
                                                            ->where('grade_level', 'Secondary')
                                                            ->pluck('name', 'id');
                                                    })
                                                    ->required()
                                                    ->searchable(),

                                                Forms\Components\Select::make('class_section_id')
                                                    ->label('Class Section')
                                                    ->options(function () {
                                                        return ClassSection::whereHas('grade', function($query) {
                                                            $query->whereIn('name', [
                                                                'Grade 8', 'Grade 9', 'Grade 10',
                                                                'Grade 11', 'Grade 12'
                                                            ]);
                                                        })
                                                        ->where('is_active', true)
                                                        ->with('grade')
                                                        ->get()
                                                        ->mapWithKeys(function ($section) {
                                                            $gradeName = $section->grade->name ?? '';
                                                            $gradeNumber = str_replace('Grade ', '', $gradeName);
                                                            $studentCount = $section->students()->where('enrollment_status', 'active')->count();
                                                            return [$section->id => "{$gradeNumber} {$section->name} ({$studentCount} students)"];
                                                        });
                                                    })
                                                    ->required()
                                                    ->searchable(),
                                            ])
                                            ->columns(2)
                                            ->minItems(1)
                                            ->required()
                                            ->helperText('Teacher will only have access to students in these class sections')
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary';
                                            }),
                                    ])
                                    ->visible(function (Forms\Get $get) {
                                        return $get('teacher_type') === 'secondary';
                                    })
                                    ->columns(2),
                            ]),

                        Forms\Components\Section::make('Account Information')
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->label('User Account')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->unique(table: User::class, column: 'email')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('password')
                                            ->password()
                                            ->required(function ($record) {
                                                return $record === null;
                                            })
                                            ->rule(Password::default())
                                            ->dehydrateStateUsing(function ($state) {
                                                return filled($state) ? Hash::make($state) : null;
                                            })
                                            ->visible(function ($record) {
                                                return $record === null;
                                            })
                                            ->dehydrated(function ($state) {
                                                return filled($state);
                                            }),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\Hidden::make('role_id')
                                            ->default(RoleConstants::TEACHER),
                                        Forms\Components\Hidden::make('status')
                                            ->default('active'),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Assignment Preview')
                            ->schema([
                                Forms\Components\Placeholder::make('assignment_preview')
                                    ->content(function (Forms\Get $get) {
                                        $teacherType = $get('teacher_type');

                                        if ($teacherType === 'primary') {
                                            $gradeId = $get('grade_id');
                                            $classSectionId = $get('class_section_id');

                                            if ($gradeId && $classSectionId) {
                                                $grade = Grade::with('subjects')->find($gradeId);
                                                $classSection = ClassSection::find($classSectionId);
                                                $studentCount = $classSection ? $classSection->students()->where('enrollment_status', 'active')->count() : 0;
                                                $subjectCount = $grade ? $grade->subjects()->where('is_active', true)->count() : 0;

                                                return "**Assignment Summary:**\n" .
                                                       "• **Role:** Class Teacher\n" .
                                                       "• **Grade:** " . ($grade ? $grade->name : 'N/A') . "\n" .
                                                       "• **Class:** " . ($classSection ? $classSection->grade->name . ' - ' . $classSection->name : 'N/A') . "\n" .
                                                       "• **Students:** {$studentCount}\n" .
                                                       "• **Subjects:** {$subjectCount} (all subjects for this grade)\n" .
                                                       "• **Access:** All students in assigned class section";
                                            }

                                            return "Select a grade and class section to see assignment preview.";
                                        }

                                        if ($teacherType === 'secondary') {
                                            $assignments = $get('subject_classes') ?? [];
                                            $isGradeTeacher = $get('is_grade_teacher');
                                            $gradeId = $get('grade_id');

                                            $content = "**Assignment Summary:**\n";
                                            $content .= "• **Role:** Subject Teacher\n";

                                            if ($isGradeTeacher && $gradeId) {
                                                $grade = Grade::find($gradeId);
                                                $content .= "• **Grade Responsibility:** " . ($grade ? $grade->name : 'N/A') . "\n";
                                            }

                                            if (!empty($assignments)) {
                                                $totalStudents = 0;
                                                $classCount = 0;
                                                $subjectNames = [];
                                                $classSectionIds = [];

                                                foreach ($assignments as $assignment) {
                                                    if (isset($assignment['subject_id']) && isset($assignment['class_section_id'])) {
                                                        $subject = Subject::find($assignment['subject_id']);
                                                        $classSection = ClassSection::find($assignment['class_section_id']);

                                                        if ($subject) {
                                                            $subjectNames[] = $subject->name;
                                                        }

                                                        if ($classSection && !in_array($classSection->id, $classSectionIds)) {
                                                            $classSectionIds[] = $classSection->id;
                                                            $totalStudents += $classSection->students()->where('enrollment_status', 'active')->count();
                                                            $classCount++;
                                                        }
                                                    }
                                                }

                                                $uniqueSubjects = array_unique($subjectNames);

                                                $content .= "• **Subjects:** " . implode(', ', $uniqueSubjects) . "\n";
                                                $content .= "• **Classes:** {$classCount}\n";
                                                $content .= "• **Total Students:** {$totalStudents}\n";
                                                $content .= "• **Access:** Only students from assigned class sections";
                                            } else {
                                                $content .= "Add subject-class assignments to see preview.";
                                            }

                                            return $content;
                                        }

                                        return "Select a teacher type to see assignment information.";
                                    })
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Additional Information')
                            ->schema([
                                Forms\Components\MarkdownEditor::make('biography')
                                    ->label('Biography/Notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('qualification')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Teacher $record): string => $record->specialization ? 'Secondary Teacher' : 'Primary Teacher'),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Assigned Grade')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not Assigned'),

                Tables\Columns\TextColumn::make('classSection.name')
                    ->label('Class Section')
                    ->placeholder('Not Assigned')
                    ->getStateUsing(function (Teacher $record): ?string {
                        if ($record->class_section_id && $record->classSection) {
                            return $record->classSection->grade->name . ' - ' . $record->classSection->name;
                        }
                        return null;
                    }),

                Tables\Columns\ToggleColumn::make('is_grade_teacher')
                    ->label('Grade Teacher')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_class_teacher')
                    ->label('Class Teacher')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subjects.name')
                    ->badge()
                    ->label('Subjects')
                    ->visible(fn ($livewire) => !in_array($livewire->getTableFilterState('filter_teacher_type')['value'] ?? '', ['primary'])),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('join_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('filter_teacher_type')
                    ->label('Teacher Type')
                    ->options([
                        'primary' => 'Primary Teacher',
                        'secondary' => 'Secondary Teacher',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'] === 'primary', function ($query) {
                            return $query->whereNull('specialization');
                        })->when($data['value'] === 'secondary', function ($query) {
                            return $query->whereNotNull('specialization');
                        });
                    }),

                Tables\Filters\SelectFilter::make('qualification')
                    ->options([
                        'Certificate' => 'Certificate',
                        'Diploma' => 'Diploma',
                        'Advanced Diploma' => 'Advanced Diploma',
                        'Degree' => 'Degree',
                        'Masters' => 'Masters',
                        'PhD' => 'PhD',
                    ]),

                Tables\Filters\SelectFilter::make('grade_id')
                    ->relationship('grade', 'name')
                    ->label('Grade'),

                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subjects', 'name')
                    ->label('Subject'),

                Tables\Filters\TernaryFilter::make('is_grade_teacher')
                    ->label('Grade Teachers'),

                Tables\Filters\TernaryFilter::make('is_class_teacher')
                    ->label('Class Teachers'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('join_date')
                    ->form([
                        Forms\Components\DatePicker::make('joined_from'),
                        Forms\Components\DatePicker::make('joined_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['joined_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('join_date', '>=', $date),
                            )
                            ->when(
                                $data['joined_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('join_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['joined_from'] ?? null) {
                            $indicators['joined_from'] = 'Joined from ' . $data['joined_from'];
                        }

                        if ($data['joined_until'] ?? null) {
                            $indicators['joined_until'] = 'Joined until ' . $data['joined_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('viewAssignments')
                        ->label('View Assignments')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('Teacher Assignments')
                        ->modalContent(function (Teacher $record) {
                            $summary = $record->getTeachingSummary();
                            $content = "<div class='space-y-4'>";
                            $content .= "<div><strong>Teacher Type:</strong> {$summary['teacher_type']}</div>";
                            $content .= "<div><strong>Assigned Grade:</strong> " . ($record->grade ? $record->grade->name : 'Not Assigned') . "</div>";
                            $content .= "<div><strong>Class Section:</strong> " . ($record->classSection ? $record->classSection->name : 'Not Assigned') . "</div>";
                            $content .= "<div><strong>Total Students:</strong> {$summary['total_students']}</div>";
                            $content .= "<div><strong>Total Subjects:</strong> {$summary['total_subjects']}</div>";
                            $content .= "<div><strong>Class Sections:</strong> {$summary['total_class_sections']}</div>";

                            if (!$summary['assignments']->isEmpty()) {
                                $content .= "<div><strong>Assignments:</strong><ul class='mt-2 space-y-1'>";
                                foreach ($summary['assignments'] as $assignment) {
                                    $classSection = $assignment['class_section'];
                                    $subjects = $assignment['subjects']->pluck('name')->join(', ');
                                    $studentCount = $assignment['student_count'];
                                    $content .= "<li>• {$classSection->grade->name} - {$classSection->name} ({$studentCount} students)<br>&nbsp;&nbsp;Subjects: {$subjects}</li>";
                                }
                                $content .= "</ul></div>";
                            }

                            $content .= "</div>";
                            return new \Illuminate\Support\HtmlString($content);
                        }),

                    Tables\Actions\Action::make('assignSubjects')
                        ->label('Assign Subjects')
                        ->icon('heroicon-o-book-open')
                        ->visible(fn (Teacher $record) => !empty($record->specialization))
                        ->form([
                            Forms\Components\Select::make('subjects')
                                ->relationship('subjects', 'name')
                                ->multiple()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Teacher $record, array $data): void {
                            $record->subjects()->sync($data['subjects']);

                            Notification::make()
                                ->title('Subjects assigned successfully')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\Action::make('resetPassword')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->form([
                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->required()
                                ->rule(Password::default())
                                ->dehydrateStateUsing(function ($state) {
                                    return Hash::make($state);
                                }),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->password()
                                ->required()
                                ->same('password'),
                        ])
                        ->action(function (Teacher $record, array $data): void {
                            if ($record->user) {
                                $record->user->update([
                                    'password' => $data['password'],
                                ]);

                                Notification::make()
                                    ->title('Password reset successfully')
                                    ->success()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('toggleStatus')
                        ->label(function (Teacher $record): string {
                            return $record->is_active ? 'Deactivate' : 'Activate';
                        })
                        ->icon(function (Teacher $record): string {
                            return $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle';
                        })
                        ->color(function (Teacher $record): string {
                            return $record->is_active ? 'danger' : 'success';
                        })
                        ->requiresConfirmation()
                        ->action(function (Teacher $record): void {
                            $record->update(['is_active' => !$record->is_active]);

                            if ($record->user) {
                                $record->user->update(['status' => $record->is_active ? 'active' : 'inactive']);
                            }

                            Notification::make()
                                ->title('Teacher status updated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activateTeachers')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_active' => true]);

                                if ($record->user) {
                                    $record->user->update(['status' => 'active']);
                                }
                            }

                            Notification::make()
                                ->title('Teachers activated successfully')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivateTeachers')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['is_active' => false]);

                                if ($record->user) {
                                    $record->user->update(['status' => 'inactive']);
                                }
                            }

                            Notification::make()
                                ->title('Teachers deactivated successfully')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubjectTeachingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'view' => Pages\ViewTeacher::route('/{record}'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['grade', 'classSection.grade']) // Eager load relationships
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * Generate a unique employee ID
     * Format: TCH-YYMMXXXXX (YY = year, MM = month, XXXXX = sequential number)
     */
    public static function generateEmployeeId(): string
    {
        // Get current year and month
        $yearMonth = Carbon::now()->format('ym');

        // Find the last teacher number for this year and month
        $lastTeacher = Teacher::where('employee_id', 'like', "TCH-{$yearMonth}%")
            ->orderBy('employee_id', 'desc')
            ->first();

        if ($lastTeacher) {
            // Extract the numeric part (last 5 digits)
            $lastNumber = (int) substr($lastTeacher->employee_id, 10);
            $newNumber = $lastNumber + 1;
        } else {
            // Start with 00001 if no teachers exist for this year/month
            $newNumber = 1;
        }

        // Format with leading zeros to ensure 5 digits
        $formattedNumber = str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        // Combine prefix, year/month and number
        return "TCH-{$yearMonth}{$formattedNumber}";
    }

    /**
     * Handle form data before saving - FIXED VERSION
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle the different teacher types
        if (isset($data['teacher_type'])) {
            if ($data['teacher_type'] === 'primary') {
                // For primary teachers - ensure all required fields are set
                $data['is_grade_teacher'] = true;
                $data['is_class_teacher'] = true;
                $data['specialization'] = null; // Primary teachers don't have specialization

                // Ensure grade_id and class_section_id are properly set
                // (They should already be set from the form, but let's be explicit)
                if (!isset($data['grade_id']) || !$data['grade_id']) {
                    throw new \Exception('Grade is required for primary teachers');
                }

                if (!isset($data['class_section_id']) || !$data['class_section_id']) {
                    throw new \Exception('Class section is required for primary teachers');
                }
            } elseif ($data['teacher_type'] === 'secondary') {
                // For secondary teachers
                $data['is_class_teacher'] = false;
                $data['class_section_id'] = null; // Secondary teachers aren't assigned to specific class sections initially

                // Only set grade_id if they are a grade teacher
                if (!($data['is_grade_teacher'] ?? false)) {
                    $data['grade_id'] = null;
                }

                // Ensure specialization is set
                if (!isset($data['specialization']) || !$data['specialization']) {
                    throw new \Exception('Specialization is required for secondary teachers');
                }
            }
        }

        // Handle role assignment for users
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $user->update(['role_id' => RoleConstants::TEACHER]);
            }
        }

        // Remove form-specific fields before saving
        unset($data['teacher_type']);
        unset($data['subject_classes']);
        unset($data['auto_assigned_subjects']);

        return $data;
    }

    /**
     * Handle data after creating the teacher
     */
    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->form->getRawState();

        // Handle subject assignments for secondary teachers
        if (isset($data['subject_classes']) && !empty($data['subject_classes'])) {
            $currentAcademicYear = AcademicYear::where('is_active', true)->first();

            foreach ($data['subject_classes'] as $assignment) {
                if (isset($assignment['subject_id']) && isset($assignment['class_section_id'])) {
                    // Create subject teaching assignment
                    $record->subjectTeachings()->create([
                        'subject_id' => $assignment['subject_id'],
                        'class_section_id' => $assignment['class_section_id'],
                        'academic_year_id' => $currentAcademicYear?->id,
                    ]);
                }
            }
        }

        // For primary teachers, assign all subjects for their grade
        if ($record->isPrimaryTeacher() && $record->grade_id && $record->class_section_id) {
            $this->assignAllSubjectsToGrade($record);
        }
    }

    /**
     * Handle data after updating the teacher
     */
    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->form->getRawState();

        // Only handle updates, not creation (afterCreate handles that)
        if (!$this->record->wasRecentlyCreated) {
            // Handle subject assignments for secondary teachers
            if (isset($data['subject_classes'])) {
                // Clear existing assignments
                $record->subjectTeachings()->delete();

                if (!empty($data['subject_classes'])) {
                    $currentAcademicYear = AcademicYear::where('is_active', true)->first();

                    foreach ($data['subject_classes'] as $assignment) {
                        if (isset($assignment['subject_id']) && isset($assignment['class_section_id'])) {
                            // Create subject teaching assignment
                            $record->subjectTeachings()->create([
                                'subject_id' => $assignment['subject_id'],
                                'class_section_id' => $assignment['class_section_id'],
                                'academic_year_id' => $currentAcademicYear?->id,
                            ]);
                        }
                    }
                }
            }

            // For primary teachers, assign all subjects for their grade
            if ($record->isPrimaryTeacher() && $record->grade_id && $record->class_section_id) {
                $this->assignAllSubjectsToGrade($record);
            }
        }
    }

    /**
     * Assign all subjects to a primary teacher's grade
     */
    private function assignAllSubjectsToGrade(Teacher $teacher): void
    {
        if (!$teacher->grade || !$teacher->classSection) {
            return;
        }

        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $subjects = $teacher->grade->subjects()->where('is_active', true)->get();

        // Clear existing assignments
        $teacher->subjectTeachings()->delete();

        // Assign all subjects
        foreach ($subjects as $subject) {
            $teacher->subjectTeachings()->create([
                'subject_id' => $subject->id,
                'class_section_id' => $teacher->class_section_id,
                'academic_year_id' => $currentAcademicYear?->id,
            ]);
        }
    }
}
