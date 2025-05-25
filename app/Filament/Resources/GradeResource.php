<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Filament\Resources\GradeResource\RelationManagers;
use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\SchoolSection;
use App\Models\AcademicYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class GradeResource extends Resource
{
    protected static ?string $model = Grade::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Configuration';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_section_id')
                    ->label('School Section')
                    ->options(SchoolSection::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Grade 1'),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('e.g. G1')
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('level')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->helperText('Numeric level for sorting and progression (e.g. 1 for Grade 1)'),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->required()
                            ->default(40)
                            ->helperText('Maximum number of students allowed in this grade'),

                        Forms\Components\TextInput::make('breakeven_number')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->helperText('Number of students that triggers the creation of a new class section'),
                    ])
                    ->columns(2),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('schoolSection.name')
                    ->label('School Section')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('level')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->label('Students')
                    ->getStateUsing(function (Grade $record) {
                        $breakdown = $record->getStudentsBreakdownAttribute();

                        if ($breakdown['total'] === 0) {
                            return '0';
                        }

                        if (isset($breakdown['note']) && str_contains($breakdown['note'], 'unassigned')) {
                            return "{$breakdown['total']} (Unassigned to sections)";
                        }

                        return $breakdown['total'];
                    })
                    ->tooltip(function (Grade $record) {
                        $breakdown = $record->getStudentsBreakdownAttribute();

                        if ($breakdown['total'] === 0) {
                            return 'No students enrolled in this grade';
                        }

                        $tooltip = "Grade {$record->name}: {$breakdown['total']} students\n";

                        if (isset($breakdown['note'])) {
                            $tooltip .= "Note: {$breakdown['note']}\n";
                        }

                        if (isset($breakdown['available_sections']) && !empty($breakdown['available_sections'])) {
                            $tooltip .= "Available sections: " . implode(', ', $breakdown['available_sections']);
                        }

                        return $tooltip;
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount([
                            'students as students_count' => function ($subQuery) {
                                $subQuery->where('enrollment_status', 'active');
                            }
                        ])->orderBy('students_count', $direction);
                    })
                    ->searchable(false)
                    ->badge()
                    ->color(function (Grade $record) {
                        $total = $record->getTotalStudentsAttribute();
                        if ($total === 0) return 'gray';

                        $breakdown = $record->getStudentsBreakdownAttribute();
                        if (isset($breakdown['note']) && str_contains($breakdown['note'], 'unassigned')) {
                            return 'warning'; // Yellow for unassigned students
                        }

                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('active_sections_count')
                    ->label('Active Sections')
                    ->getStateUsing(function (Grade $record) {
                        return $record->activeClassSections()->count();
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('utilization')
                    ->label('Utilization')
                    ->getStateUsing(function (Grade $record) {
                        $totalStudents = $record->getTotalStudentsAttribute();
                        $capacity = $record->capacity;

                        if ($capacity == 0) return 'N/A';

                        $percentage = round(($totalStudents / $capacity) * 100, 1);
                        return "{$percentage}%";
                    })
                    ->color(function (Grade $record) {
                        $totalStudents = $record->getTotalStudentsAttribute();
                        $capacity = $record->capacity;

                        if ($capacity == 0) return 'gray';

                        $percentage = ($totalStudents / $capacity) * 100;

                        if ($percentage >= 90) return 'danger';
                        if ($percentage >= 70) return 'warning';
                        return 'success';
                    })
                    ->sortable(false),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_section')
                    ->relationship('schoolSection', 'name'),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active only')
                    ->toggle(),

                Tables\Filters\Filter::make('has_students')
                    ->query(function (Builder $query): Builder {
                        return $query->whereHas('students', function ($subQuery) {
                            $subQuery->where('enrollment_status', 'active');
                        });
                    })
                    ->label('Has students')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('viewStudentBreakdown')
                    ->label('Student Details')
                    ->icon('heroicon-o-users')
                    ->color('info')
                    ->modalContent(function (Grade $record) {
                        $breakdown = $record->getStudentsBreakdownAttribute();
                        $students = $record->students()->where('enrollment_status', 'active')->get();

                        $content = "<div class='space-y-4'>";
                        $content .= "<h3 class='text-lg font-semibold'>{$record->name} - Student Details</h3>";

                        if ($breakdown['total'] === 0) {
                            $content .= "<p class='text-gray-500'>No students enrolled in this grade.</p>";
                        } else {
                            $content .= "<div class='space-y-3'>";

                            // Summary section
                            $content .= "<div class='p-3 bg-blue-50 rounded border-l-4 border-blue-400'>";
                            $content .= "<div class='flex justify-between items-center'>";
                            $content .= "<span class='font-bold text-blue-800'>Total Students</span>";
                            $content .= "<span class='text-blue-800 font-bold text-lg'>{$breakdown['total']}</span>";
                            $content .= "</div>";
                            $content .= "</div>";

                            // Warning if students are unassigned
                            if (isset($breakdown['note']) && str_contains($breakdown['note'], 'unassigned')) {
                                $content .= "<div class='p-3 bg-yellow-50 rounded border-l-4 border-yellow-400'>";
                                $content .= "<p class='text-yellow-800 font-medium'>⚠️ Students need to be assigned to class sections</p>";
                                if (isset($breakdown['available_sections']) && !empty($breakdown['available_sections'])) {
                                    $content .= "<p class='text-yellow-700 text-sm mt-1'>Available sections: " . implode(', ', $breakdown['available_sections']) . "</p>";
                                }
                                $content .= "</div>";
                            }

                            // Student list
                            $content .= "<div class='space-y-2'>";
                            $content .= "<h4 class='font-semibold text-gray-700'>Enrolled Students:</h4>";
                            foreach ($students as $student) {
                                $content .= "<div class='flex justify-between items-center p-2 bg-gray-50 rounded'>";
                                $content .= "<div>";
                                $content .= "<span class='font-medium'>{$student->name}</span>";
                                $content .= "<span class='text-gray-500 text-sm ml-2'>({$student->student_id_number})</span>";
                                $content .= "</div>";
                                $sectionText = $student->class_section_id ? 'Assigned to section' : 'No section assigned';
                                $content .= "<span class='text-xs px-2 py-1 rounded " . ($student->class_section_id ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') . "'>{$sectionText}</span>";
                                $content .= "</div>";
                            }
                            $content .= "</div>";
                        }

                        $content .= "<div class='text-sm text-gray-600 space-y-1 mt-4 pt-4 border-t'>";
                        $content .= "<p><strong>Grade Capacity:</strong> {$record->capacity} students</p>";
                        $content .= "<p><strong>Breakeven Number:</strong> {$record->breakeven_number} students</p>";
                        if ($breakdown['total'] > 0) {
                            $utilization = round(($breakdown['total'] / $record->capacity) * 100, 1);
                            $content .= "<p><strong>Current Utilization:</strong> {$utilization}%</p>";
                        }
                        $content .= "</div>";
                        $content .= "</div>";

                        return new \Illuminate\Support\HtmlString($content);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Tables\Actions\Action::make('createClassSection')
                    ->label('Add Class Section')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(function () {
                                return AcademicYear::query()
                                    ->orderByDesc('is_active')
                                    ->orderByDesc('start_date')
                                    ->pluck('name', 'id');
                            })
                            ->default(function () {
                                return AcademicYear::where('is_active', true)->first()?->id;
                            })
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Section Name')
                            ->required()
                            ->placeholder('e.g. A or Red'),

                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->label('Section Capacity')
                            ->required(),

                        Forms\Components\Select::make('class_teacher_id')
                            ->label('Class Teacher')
                            ->options(function () {
                                $teacherRoleId = \App\Models\Role::where('name', 'teacher')->value('id');

                                return \App\Models\Employee::where('role_id', $teacherRoleId)
                                    ->where('status', 'active')
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                    ])
                    ->action(function (Grade $record, array $data) {
                        // Generate section code
                        $code = ClassSection::generateCode($record->code, $data['name']);

                        // Create the class section
                        $section = $record->classSections()->create([
                            'academic_year_id' => $data['academic_year_id'],
                            'name' => $data['name'],
                            'code' => $code,
                            'capacity' => $data['capacity'],
                            'class_teacher_id' => $data['class_teacher_id'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Class section created')
                            ->body("Created section {$section->name} for {$record->name}")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('manageCapacity')
                    ->label('Manage Capacity')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->action(function (Grade $record, array $data) {
                        $record->update([
                            'capacity' => $data['capacity'],
                            'breakeven_number' => $data['breakeven_number'],
                        ]);

                        Notification::make()
                            ->title('Capacity updated')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Forms\Components\TextInput::make('capacity')
                            ->numeric()
                            ->required()
                            ->default(fn (Grade $record) => $record->capacity),

                        Forms\Components\TextInput::make('breakeven_number')
                            ->numeric()
                            ->required()
                            ->default(fn (Grade $record) => $record->breakeven_number),

                        Forms\Components\Placeholder::make('current_students')
                            ->label('Current Students')
                            ->content(fn (Grade $record) => $record->getTotalStudentsAttribute()),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ClassSectionsRelationManager::class,
            // RelationManagers\SubjectsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount([
            'students as students_count' => function ($query) {
                $query->where('enrollment_status', 'active');
            }
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGrades::route('/'),
            'create' => Pages\CreateGrade::route('/create'),
            'view' => Pages\ViewGrade::route('/{record}'),
            'edit' => Pages\EditGrade::route('/{record}/edit'),
        ];
    }
}
