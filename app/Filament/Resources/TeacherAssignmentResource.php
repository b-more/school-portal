<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\ClassSection;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Constants\RoleConstants;
use Filament\Notifications\Notification;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = Teacher::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Teacher Assignments';
    protected static ?string $slug = 'teacher-assignments';
    protected static ?int $navigationSort = 8;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Teacher Information')
                ->schema([
                    Forms\Components\Placeholder::make('teacher_name')
                        ->label('Teacher')
                        ->content(fn ($record) => $record?->name ?? 'N/A'),

                    Forms\Components\Placeholder::make('teacher_type')
                        ->label('Teacher Type')
                        ->content(function ($record) {
                            if (!$record) return 'N/A';
                            $specialization = $record->specialization;
                            if (in_array($specialization, ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'English', 'History', 'Geography'])) {
                                return 'Secondary';
                            }
                            return 'Primary/ECL';
                        }),
                ])
                ->columns(2),

            Forms\Components\Section::make('Class Assignment')
                ->description('Assign teacher to class sections. Primary teachers will automatically be assigned all primary subjects.')
                ->schema([
                    Forms\Components\Select::make('class_section_assignments')
                        ->label('Assign to Class Section(s)')
                        ->options(function () {
                            return ClassSection::with('grade')
                                ->where('is_active', true)
                                ->get()
                                ->mapWithKeys(function ($section) {
                                    $gradeName = $section->grade?->name ?? 'Unknown';
                                    return [$section->id => "{$gradeName} {$section->name}"];
                                })
                                ->sort()
                                ->toArray();
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->helperText('Select class section(s) this teacher will teach'),
                ]),

            Forms\Components\Section::make('Subject Assignment')
                ->description('For secondary teachers, select specific subjects. For primary teachers, leave empty to auto-assign all primary subjects.')
                ->schema([
                    Forms\Components\Select::make('subject_assignments')
                        ->label('Subjects')
                        ->options(function () {
                            return Subject::where('is_active', true)
                                ->orderBy('grade_level')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(function ($subject) {
                                    return [$subject->id => "{$subject->name} ({$subject->grade_level})"];
                                })
                                ->toArray();
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->helperText('Leave empty for primary teachers to auto-assign all primary subjects'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Specialization')
                    ->default('Primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('class_section_display')
                    ->label('Class Section')
                    ->getStateUsing(function (Teacher $record) {
                        if (!$record->class_section_id) return 'Not Assigned';
                        $section = $record->classSection;
                        if (!$section) return 'Not Assigned';
                        return ($section->grade?->name ?? '') . ' ' . $section->name;
                    })
                    ->badge()
                    ->color(fn ($state) => $state === 'Not Assigned' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('subjects_count')
                    ->label('Subjects')
                    ->getStateUsing(function (Teacher $record) {
                        return DB::table('subject_teachings')
                            ->where('teacher_id', $record->id)
                            ->distinct('subject_id')
                            ->count('subject_id');
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students')
                    ->getStateUsing(function (Teacher $record) {
                        if (!$record->class_section_id) return 0;
                        return DB::table('students')
                            ->where('class_section_id', $record->class_section_id)
                            ->count();
                    })
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'primary' : 'gray'),

                Tables\Columns\IconColumn::make('is_class_teacher')
                    ->label('Class Teacher')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('assigned')
                    ->query(fn (Builder $query) => $query->whereNotNull('class_section_id'))
                    ->label('Assigned Only')
                    ->toggle(),

                Tables\Filters\Filter::make('unassigned')
                    ->query(fn (Builder $query) => $query->whereNull('class_section_id'))
                    ->label('Unassigned Only')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('assign_class_teacher')
                    ->label('Assign as Class Teacher')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('class_section_id')
                            ->label('Class Section')
                            ->options(function () {
                                return ClassSection::with('grade')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($section) {
                                        $gradeName = $section->grade?->name ?? 'Unknown';
                                        $currentTeacher = Teacher::where('class_section_id', $section->id)
                                            ->where('is_class_teacher', true)
                                            ->first();
                                        $label = "{$gradeName} {$section->name}";
                                        if ($currentTeacher) {
                                            $label .= " (Class Teacher: {$currentTeacher->name})";
                                        }
                                        return [$section->id => $label];
                                    })
                                    ->sort()
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) return;

                                $currentTeacher = Teacher::where('class_section_id', $state)
                                    ->where('is_class_teacher', true)
                                    ->first();
                                if ($currentTeacher) {
                                    $set('warning_message', "Warning: {$currentTeacher->name} is the current Class Teacher. They will be replaced as Class Teacher.");
                                } else {
                                    $set('warning_message', null);
                                }
                            }),

                        Forms\Components\Placeholder::make('warning_message')
                            ->label('')
                            ->content(fn ($get) => $get('warning_message'))
                            ->visible(fn ($get) => !empty($get('warning_message')))
                            ->extraAttributes(['class' => 'text-danger-600 font-semibold']),

                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content('As Class Teacher, all primary subjects will be automatically assigned.')
                            ->extraAttributes(['class' => 'text-primary-600']),
                    ])
                    ->action(function (Teacher $record, array $data) {
                        $classSectionId = $data['class_section_id'];

                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                        if (!$currentAcademicYear) {
                            Notification::make()->title('Error')->body('No active academic year found.')->danger()->send();
                            return;
                        }

                        $classSection = ClassSection::with('grade')->find($classSectionId);
                        $gradeName = $classSection->grade?->name ?? '';
                        $isPrimaryLevel = in_array($gradeName, ['Baby Class', 'Middle Class', 'Reception']) ||
                                         (preg_match('/Grade (\d+)/', $gradeName, $matches) && (int)$matches[1] <= 7);

                        // Remove existing class teacher (but keep them as subject teacher if they have subjects)
                        $existingClassTeacher = Teacher::where('class_section_id', $classSectionId)
                            ->where('is_class_teacher', true)
                            ->where('id', '!=', $record->id)
                            ->first();

                        if ($existingClassTeacher) {
                            $existingClassTeacher->update([
                                'is_class_teacher' => false,
                                'class_section_id' => null,
                            ]);
                            // Remove their subject teachings
                            DB::table('subject_teachings')
                                ->where('teacher_id', $existingClassTeacher->id)
                                ->where('class_section_id', $classSectionId)
                                ->delete();
                        }

                        // Set new class teacher
                        $record->update([
                            'class_section_id' => $classSectionId,
                            'is_class_teacher' => true,
                        ]);

                        // Update class_sections.class_teacher_id for ClassSectionResource
                        $classSection->update(['class_teacher_id' => $record->id]);

                        // Clear existing subject teachings for this teacher
                        DB::table('subject_teachings')->where('teacher_id', $record->id)->delete();

                        // Auto-assign all primary subjects for class teacher
                        $subjectIds = $isPrimaryLevel
                            ? Subject::where('grade_level', 'Primary')->where('is_active', true)->pluck('id')->toArray()
                            : Subject::where('grade_level', 'Secondary')->where('is_active', true)->pluck('id')->toArray();

                        $insertData = [];
                        foreach ($subjectIds as $subjectId) {
                            $insertData[] = [
                                'teacher_id' => $record->id,
                                'subject_id' => $subjectId,
                                'class_section_id' => $classSectionId,
                                'academic_year_id' => $currentAcademicYear->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        if (!empty($insertData)) {
                            DB::table('subject_teachings')->insert($insertData);
                        }

                        // Sync with class_teacher table for SchoolClassResource
                        self::syncClassTeacherTable($record, $classSection, 'class_teacher', $existingClassTeacher);

                        Notification::make()
                            ->title('Class Teacher Assigned')
                            ->body("Assigned {$record->name} as Class Teacher for {$gradeName} {$classSection->name} with " . count($subjectIds) . " subjects.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('assign_subject_teacher')
                    ->label('Assign as Subject Teacher')
                    ->icon('heroicon-o-book-open')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('class_section_id')
                            ->label('Class Section')
                            ->options(function () {
                                return ClassSection::with('grade')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($section) {
                                        $gradeName = $section->grade?->name ?? 'Unknown';
                                        return [$section->id => "{$gradeName} {$section->name}"];
                                    })
                                    ->sort()
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('subjects')
                            ->label('Subjects to Teach')
                            ->options(function () {
                                return Subject::where('is_active', true)
                                    ->orderBy('grade_level')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function ($subject) {
                                        return [$subject->id => "{$subject->name} ({$subject->grade_level})"];
                                    })
                                    ->toArray();
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select specific subjects this teacher will teach'),
                    ])
                    ->action(function (Teacher $record, array $data) {
                        $classSectionId = $data['class_section_id'];
                        $subjectIds = $data['subjects'];

                        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
                        if (!$currentAcademicYear) {
                            Notification::make()->title('Error')->body('No active academic year found.')->danger()->send();
                            return;
                        }

                        $classSection = ClassSection::with('grade')->find($classSectionId);

                        // Add subject teachings (don't clear existing - add to them)
                        $insertData = [];
                        foreach ($subjectIds as $subjectId) {
                            // Check if already exists
                            $exists = DB::table('subject_teachings')
                                ->where('teacher_id', $record->id)
                                ->where('subject_id', $subjectId)
                                ->where('class_section_id', $classSectionId)
                                ->exists();

                            if (!$exists) {
                                $insertData[] = [
                                    'teacher_id' => $record->id,
                                    'subject_id' => $subjectId,
                                    'class_section_id' => $classSectionId,
                                    'academic_year_id' => $currentAcademicYear->id,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        if (!empty($insertData)) {
                            DB::table('subject_teachings')->insert($insertData);
                        }

                        // Sync with class_teacher table for SchoolClassResource (as subject_teacher role)
                        self::syncClassTeacherTable($record, $classSection, 'subject_teacher');

                        Notification::make()
                            ->title('Subject Teacher Assigned')
                            ->body("Assigned {$record->name} to teach " . count($subjectIds) . " subject(s) in " . ($classSection->grade?->name ?? '') . " {$classSection->name}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('unassign')
                    ->label('Unassign')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Teacher $record) => $record->class_section_id !== null)
                    ->action(function (Teacher $record) {
                        // Get the class section before clearing
                        $classSectionId = $record->class_section_id;

                        // Clear class_sections.class_teacher_id
                        if ($classSectionId) {
                            ClassSection::where('id', $classSectionId)
                                ->where('class_teacher_id', $record->id)
                                ->update(['class_teacher_id' => null]);
                        }

                        // Clear class section assignment
                        $record->update([
                            'class_section_id' => null,
                            'is_class_teacher' => false,
                        ]);

                        // Clear subject teachings
                        DB::table('subject_teachings')
                            ->where('teacher_id', $record->id)
                            ->delete();

                        // Sync with class_teacher table - remove all entries for this teacher
                        DB::table('class_teacher')
                            ->where('teacher_id', $record->id)
                            ->delete();

                        Notification::make()
                            ->title('Teacher Unassigned')
                            ->body("{$record->name} has been unassigned from their class.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAssignments::route('/'),
            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),
            'view' => Pages\ViewTeacherAssignment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['classSection', 'classSection.grade'])
            ->whereHas('user', function($query) {
                $query->where('role_id', RoleConstants::TEACHER);
            });
    }

    /**
     * Sync the class_teacher table to keep SchoolClassResource in sync
     */
    protected static function syncClassTeacherTable(Teacher $teacher, ClassSection $classSection, string $role = 'class_teacher', ?Teacher $existingClassTeacher = null): void
    {
        // Find the corresponding school_class by matching the full name
        $fullClassName = ($classSection->grade?->name ?? '') . ' ' . $classSection->name;
        $schoolClass = SchoolClass::where('name', $fullClassName)->first();

        if (!$schoolClass) {
            return; // No matching school_class found
        }

        // If replacing an existing class teacher, remove their entry
        if ($existingClassTeacher) {
            DB::table('class_teacher')
                ->where('teacher_id', $existingClassTeacher->id)
                ->where('class_id', $schoolClass->id)
                ->where('role', 'class_teacher')
                ->delete();
        }

        // Check if entry already exists
        $exists = DB::table('class_teacher')
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $schoolClass->id)
            ->exists();

        if ($exists) {
            // Update role if changing
            DB::table('class_teacher')
                ->where('teacher_id', $teacher->id)
                ->where('class_id', $schoolClass->id)
                ->update([
                    'role' => $role,
                    'is_primary' => $role === 'class_teacher',
                    'updated_at' => now(),
                ]);
        } else {
            // Insert new entry
            DB::table('class_teacher')->insert([
                'teacher_id' => $teacher->id,
                'class_id' => $schoolClass->id,
                'role' => $role,
                'is_primary' => $role === 'class_teacher',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
