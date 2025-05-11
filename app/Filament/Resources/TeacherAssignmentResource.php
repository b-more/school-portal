<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = Teacher::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Teacher Assignments';
    protected static ?string $slug = 'teacher-assignments';
    protected static ?int $navigationSort = 8;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('id')
                ->label('Teacher')
                ->options(function () {
                    // Use Teacher model with proper role_id check
                    return Teacher::whereHas('user', function($query) {
                        $query->where('role_id', RoleConstants::TEACHER);
                    })
                    ->orderBy('name')
                    ->pluck('name', 'id');
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    // Log for debugging
                    Log::info('Teacher selected', ['id' => $state]);

                    // Get the teacher and set info
                    if ($state) {
                        $teacher = Teacher::find($state);
                        $employee = $teacher?->employee;
                        if ($employee) {
                            $set('department', $employee->department);
                            Log::info('Setting department', ['department' => $employee->department]);
                        }
                    } else {
                        $set('department', null);
                    }
                }),

            Forms\Components\TextInput::make('department')
                ->label('Department')
                ->disabled()
                ->dehydrated(false),

            // For ECL/Primary Teachers
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Select::make('class_assignments')
                        ->label('Assign to Classes')
                        ->options(function () {
                            return SchoolClass::whereIn('department', ['ECL', 'Primary'])
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        })
                        ->multiple()
                        ->searchable()
                        ->saveRelationshipsUsing(function ($record, $state) {
                            $classData = [];
                            foreach ($state as $classId) {
                                $classData[$classId] = [
                                    'role' => 'class_teacher',
                                    'is_primary' => true,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }

                            // Clear existing and set new assignments
                            DB::table('class_teacher')
                                ->where('employee_id', $record->employee_id)
                                ->delete();

                            if (!empty($classData)) {
                                // Use employee_id for the pivot table
                                $employee = $record->employee;
                                if ($employee) {
                                    $employee->classes()->attach($classData);
                                }
                            }
                        })
                        ->visible(function (callable $get) {
                            $teacherId = $get('id');
                            if (!$teacherId) return false;

                            $teacher = Teacher::find($teacherId);
                            $employee = $teacher?->employee;
                            return $employee && in_array($employee->department, ['ECL', 'Primary']);
                        }),
                ])
                ->visible(function (callable $get) {
                    $teacherId = $get('id');
                    if (!$teacherId) return false;

                    $teacher = Teacher::find($teacherId);
                    $employee = $teacher?->employee;
                    return $employee && in_array($employee->department, ['ECL', 'Primary']);
                }),

            // For Secondary Teachers
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Select::make('subject_assignments')
                        ->label('Assign Subjects')
                        ->options(function () {
                            return Subject::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->multiple()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $state) {
                            // Log for debugging
                            Log::info('Subject assignments updated', ['subjects' => $state]);

                            // Clear class_subject_assignments when subjects change
                            $set('class_subject_assignments', []);
                        })
                        ->saveRelationshipsUsing(function ($record, $state) {
                            // Update subject assignments
                            $record->subjects()->sync($state);
                        }),

                    Forms\Components\Repeater::make('class_subject_assignments')
                        ->schema([
                            Forms\Components\Select::make('subject_id')
                                ->label('Subject')
                                ->options(function (callable $get) {
                                    // Try to get subject assignments from the form
                                    $subjectIds = $get('../subject_assignments');

                                    // Log for debugging
                                    Log::info('Getting subjects with path ../subject_assignments', [
                                        'subject_ids' => $subjectIds
                                    ]);

                                    if (empty($subjectIds)) {
                                        $alternativePath = $get('../../subject_assignments');
                                        Log::info('Trying alternative path ../../subject_assignments', [
                                            'alternative_subject_ids' => $alternativePath
                                        ]);

                                        $subjectIds = $alternativePath;
                                    }

                                    // Fall back to all subjects if we can't get the selected ones
                                    if (empty($subjectIds)) {
                                        Log::info('Falling back to all subjects');
                                        return Subject::orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }

                                    return Subject::whereIn('id', $subjectIds)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->searchable()
                                ->reactive()
                                ->required(),

                            Forms\Components\Select::make('school_class_ids')
                                ->label('Classes')
                                ->options(function () {
                                    return SchoolClass::where('department', 'Secondary')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray();
                                })
                                ->multiple()
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(2)
                        ->itemLabel(function (array $state) {
                            $subjectId = $state['subject_id'] ?? null;
                            $subjectName = $subjectId ?
                                (Subject::find($subjectId)?->name ?? "Subject #$subjectId") :
                                'No subject selected';

                            $classCount = isset($state['school_class_ids']) ? count($state['school_class_ids']) : 0;
                            return "$subjectName ($classCount classes)";
                        })
                ])
                ->visible(function (callable $get) {
                    $teacherId = $get('id');
                    if (!$teacherId) return false;

                    $teacher = Teacher::find($teacherId);
                    $employee = $teacher?->employee;
                    return $employee && $employee->department === 'Secondary';
                }),
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

                Tables\Columns\TextColumn::make('employee.department')
                    ->label('Department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('class_sections_count')
                    ->counts('classSections')
                    ->label('Classes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subjects_count')
                    ->counts('subjects')
                    ->label('Subjects')
                    ->sortable(),

                Tables\Columns\TextColumn::make('is_class_teacher')
                    ->label('Class Teacher')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('is_grade_teacher')
                    ->label('Grade Teacher')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee.department')
                    ->label('Department')
                    ->relationship('employee', 'department')
                    ->options([
                        'ECL' => 'ECL',
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                    ]),

                Tables\Filters\Filter::make('has_assignments')
                    ->query(function (Builder $query) {
                        return $query->whereHas('classSections')->orWhereHas('subjects');
                    })
                    ->label('Has Assignments')
                    ->toggle(),

                Tables\Filters\Filter::make('class_teachers')
                    ->query(function (Builder $query) {
                        return $query->where('is_class_teacher', true);
                    })
                    ->label('Class Teachers Only')
                    ->toggle(),

                Tables\Filters\Filter::make('grade_teachers')
                    ->query(function (Builder $query) {
                        return $query->where('is_grade_teacher', true);
                    })
                    ->label('Grade Teachers Only')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Teacher $record) => route('filament.admin.resources.teacher-assignments.edit', $record)),

                Tables\Actions\ViewAction::make()
                    ->label('View Assignments')
                    ->url(fn (Teacher $record) => route('filament.admin.resources.teacher-assignments.view', $record)),

                Tables\Actions\Action::make('set_class_teacher')
                    ->label('Set as Class Teacher')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->action(function (Teacher $record) {
                        $record->update(['is_class_teacher' => !$record->is_class_teacher]);

                        Notification::make()
                            ->title('Class Teacher Status Updated')
                            ->body("{$record->name} is now " . ($record->is_class_teacher ? 'a' : 'not a') . " class teacher.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('set_grade_teacher')
                    ->label('Set as Grade Teacher')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->action(function (Teacher $record) {
                        $record->update(['is_grade_teacher' => !$record->is_grade_teacher]);

                        Notification::make()
                            ->title('Grade Teacher Status Updated')
                            ->body("{$record->name} is now " . ($record->is_grade_teacher ? 'a' : 'not a') . " grade teacher.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_class_teachers')
                        ->label('Set as Class Teachers')
                        ->icon('heroicon-o-academic-cap')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['is_class_teacher' => true]);
                                $count++;
                            }

                            Notification::make()
                                ->title('Updated Class Teachers')
                                ->body("Set {$count} teachers as class teachers.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherAssignments::route('/'),
            'create' => Pages\CreateTeacherAssignment::route('/create'),
            'edit' => Pages\EditTeacherAssignment::route('/{record}/edit'),
            'view' => Pages\ViewTeacherAssignment::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['employee', 'user'])
            ->whereHas('user', function($query) {
                $query->where('role_id', RoleConstants::TEACHER);
            });
    }
}
