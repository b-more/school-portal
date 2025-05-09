<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherAssignmentResource\Pages;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherAssignmentResource extends Resource
{
    protected static ?string $model = Employee::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Teacher Assignments';
    protected static ?string $slug = 'teacher-assignments';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('employee_id')
                ->label('Teacher')
                ->options(
                    Employee::where('role', 'teacher')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    // Log for debugging
                    Log::info('Teacher selected', ['id' => $state]);

                    // Get the employee and set the department
                    if ($state) {
                        $employee = Employee::find($state);
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
                        ->options(
                            SchoolClass::whereIn('department', ['ECL', 'Primary'])
                                ->orderBy('name')
                                ->pluck('name', 'id')
                        )
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
                                ->where('employee_id', $record->id)
                                ->delete();

                            if (!empty($classData)) {
                                $record->classes()->attach($classData);
                            }
                        })
                        ->visible(function (callable $get) {
                            $employeeId = $get('employee_id');
                            if (!$employeeId) return false;

                            $employee = Employee::find($employeeId);
                            return $employee && in_array($employee->department, ['ECL', 'Primary']);
                        }),
                ])
                ->visible(function (callable $get) {
                    $employeeId = $get('employee_id');
                    if (!$employeeId) return false;

                    $employee = Employee::find($employeeId);
                    return $employee && in_array($employee->department, ['ECL', 'Primary']);
                }),

            // For Secondary Teachers
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Select::make('subject_assignments')
                        ->label('Assign Subjects')
                        ->options(
                            Subject::orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
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
                                    // The path might be different depending on how the form state is structured
                                    // Try different paths if this one doesn't work
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
                                ->options(
                                    SchoolClass::where('department', 'Secondary')
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
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
                    $employeeId = $get('employee_id');
                    if (!$employeeId) return false;

                    $employee = Employee::find($employeeId);
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

                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('classes_count')
                    ->counts('classes')
                    ->label('Classes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subjects_count')
                    ->counts('subjects')
                    ->label('Subjects')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'ECL' => 'ECL',
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                    ]),

                Tables\Filters\Filter::make('has_assignments')
                    ->query(function (Builder $query) {
                        return $query->whereHas('classes')->orWhereHas('subjects');
                    })
                    ->label('Has Assignments')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn (Employee $record) => route('filament.admin.resources.teacher-assignments.edit', $record)),

                Tables\Actions\ViewAction::make()
                    ->label('View Assignments')
                    ->url(fn (Employee $record) => route('filament.admin.resources.teacher-assignments.view', $record)),
            ])
            ->bulkActions([
                // No bulk actions needed here
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
            ->where('role', 'teacher');
    }
}
