<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\ClassSection;
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
                                        } else {
                                            $set('is_class_teacher', false);
                                        }
                                    })
                                    ->columnSpanFull(),

                                // For Primary Teachers (Baby Class to Grade 7)
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('primary_teacher_info')
                                            ->content('Primary teachers are responsible for one class and teach all subjects for that class.')
                                            ->columnSpanFull(),

                                        Forms\Components\Select::make('primary_grade_id')
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
                                            ->live()
                                            ->afterStateUpdated(fn (Forms\Set $set) => $set('primary_class_section_id', null))
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'primary';
                                            }),

                                        Forms\Components\Select::make('primary_class_section_id')
                                            ->label('Assigned Class Section')
                                            ->options(function (Forms\Get $get) {
                                                $gradeId = $get('primary_grade_id');
                                                if (!$gradeId) {
                                                    return [];
                                                }

                                                return ClassSection::where('grade_id', $gradeId)
                                                    ->where('is_active', true)
                                                    ->with('grade')
                                                    ->get()
                                                    ->mapWithKeys(function ($section) {
                                                        return [$section->id => "{$section->grade->name} - {$section->name}"];
                                                    });
                                            })
                                            ->preload(false)
                                            ->searchable()
                                            ->required()
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'primary' && $get('primary_grade_id');
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
                                            ->content('Secondary teachers specialize in specific subjects and can teach across multiple classes and grades.')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('specialization')
                                            ->label('Subject Specialization')
                                            ->maxLength(255)
                                            ->required()
                                            ->visible(function (Forms\Get $get) {
                                                return $get('teacher_type') === 'secondary';
                                            }),

                                        Forms\Components\Toggle::make('is_grade_teacher')
                                            ->label('Is Grade Teacher?')
                                            ->helperText('Grade teachers have additional responsibilities for overseeing an entire grade level')
                                            ->default(false)
                                            ->reactive()
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
                                                            return [$section->id => "{$gradeNumber} {$section->name}"];
                                                        });
                                                    })
                                                    ->required()
                                                    ->searchable(),
                                            ])
                                            ->columns(2)
                                            ->minItems(1)
                                            ->required()
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
                        Forms\Components\Section::make('Schedule Summary')
                            ->schema([
                                Forms\Components\Placeholder::make('schedule_info')
                                    ->content(function (Forms\Get $get) {
                                        if ($get('teacher_type') === 'primary') {
                                            return 'As a primary teacher, you will be teaching all subjects to your assigned class.';
                                        } elseif ($get('teacher_type') === 'secondary') {
                                            return 'As a secondary teacher, you will teach your specialized subjects across multiple classes.';
                                        } else {
                                            return 'Please select a teacher type to see schedule information.';
                                        }
                                    }),
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

                Tables\Columns\TextColumn::make('subjects.name')
                    ->badge()
                    ->label('Subjects')
                    ->visible(fn ($livewire) => !in_array($livewire->getTableFilterState('filter_teacher_type')['value'] ?? '', ['primary'])),

                Tables\Columns\TextColumn::make('classSection.name')
                    ->label('Class Section')
                    ->placeholder('N/A'),

                Tables\Columns\ToggleColumn::make('is_grade_teacher')
                    ->label('Grade Teacher')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Assigned Grade')
                    ->searchable(),

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

                    Tables\Actions\Action::make('assignClassSections')
                        ->label('Assign Class Sections')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Forms\Components\Select::make('class_sections')
                                ->label('Class Sections')
                                ->multiple()
                                ->options(function (Teacher $record) {
                                    $query = ClassSection::query();

                                    // Filter classes based on teacher type
                                    if (empty($record->specialization)) {
                                        // Primary teacher - show primary grades
                                        $query->whereHas('grade', function($q) {
                                            $q->whereIn('name', [
                                                'Baby Class', 'Middle Class', 'Reception',
                                                'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4',
                                                'Grade 5', 'Grade 6', 'Grade 7'
                                            ]);
                                        });
                                    } else {
                                        // Secondary teacher - show secondary grades
                                        $query->whereHas('grade', function($q) {
                                            $q->whereIn('name', [
                                                'Grade 8', 'Grade 9', 'Grade 10',
                                                'Grade 11', 'Grade 12'
                                            ]);
                                        });
                                    }

                                    return $query->with('grade')->get()->mapWithKeys(function ($section) {
                                        $gradeName = $section->grade->name ?? '';
                                        $gradeNumber = str_replace('Grade ', '', $gradeName);
                                        return [$section->id => "{$gradeNumber} {$section->name}"];
                                    });
                                })
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Teacher $record, array $data): void {
                            // If assigning as class teacher, update class sections
                            $record->update(['is_class_teacher' => true]);

                            // Update the class sections
                            foreach ($data['class_sections'] as $classSectionId) {
                                ClassSection::where('id', $classSectionId)
                                    ->update(['class_teacher_id' => $record->id]);
                            }

                            Notification::make()
                                ->title('Class sections assigned successfully')
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

    public function afterSave(array $data): void
    {
        /** @var Teacher $teacher */
        $teacher = $this->record;

        // Handle primary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'primary' && isset($data['primary_class_section_id'])) {
            // Set the teacher for the assigned class section
            $classSection = ClassSection::find($data['primary_class_section_id']);
            if ($classSection) {
                $classSection->update(['class_teacher_id' => $teacher->id]);
                $teacher->update(['class_section_id' => $data['primary_class_section_id']]);
            }
        }

        // Handle secondary teachers
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'secondary' && isset($data['subject_classes'])) {
            // Clear existing subject teachings for this teacher
            $teacher->subjectTeachings()->delete();

            // Add new subject teachings
            foreach ($data['subject_classes'] as $assignment) {
                $teacher->subjectTeachings()->create([
                    'subject_id' => $assignment['subject_id'],
                    'class_section_id' => $assignment['class_section_id'],
                    'academic_year_id' => ClassSection::find($assignment['class_section_id'])->academic_year_id ?? null,
                ]);
            }

            // Sync subjects
            $subjectIds = collect($data['subject_classes'])->pluck('subject_id')->unique()->toArray();
            $teacher->subjects()->sync($subjectIds);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle the different teacher types
        if (isset($data['teacher_type']) && $data['teacher_type'] === 'primary') {
            // For primary teachers, use the primary grade ID
            $data['grade_id'] = $data['primary_grade_id'] ?? null;
            $data['is_grade_teacher'] = true;
            $data['is_class_teacher'] = true;
            $data['class_section_id'] = $data['primary_class_section_id'] ?? null;
        }

        // Handle role assignment for users
        if (isset($data['user_id']) && isset($data['teacher_type'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $user->update(['role_id' => RoleConstants::TEACHER]);
            }
        }

        // Remove form-specific fields before saving
        unset($data['teacher_type']);
        unset($data['primary_grade_id']);
        unset($data['primary_class_section_id']);
        unset($data['subject_classes']);

        return $data;
    }
}
