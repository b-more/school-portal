<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeResource\Pages;
use App\Filament\Resources\GradeResource\RelationManagers;
use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\SchoolSection;
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

                Tables\Columns\TextColumn::make('total_students')
                    ->label('Students')
                    ->getStateUsing(fn (Grade $record) => $record->getTotalStudentsAttribute())
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount('students')
                            ->orderBy('students_count', $direction);
                    }),

                Tables\Columns\TextColumn::make('classSections_count')
                    ->counts('classSections')
                    ->label('Sections'),

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('createClassSection')
                    ->label('Add Class Section')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(function () {
                                return \App\Models\AcademicYear::query()
                                    ->orderByDesc('is_active')
                                    ->orderByDesc('start_date')
                                    ->pluck('name', 'id');
                            })
                            ->default(function () {
                                return \App\Models\AcademicYear::where('is_active', true)->first()?->id;
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
    return parent::getEloquentQuery()->withCount('students');
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
