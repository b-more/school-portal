<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassSectionResource\Pages;
use App\Filament\Resources\ClassSectionResource\RelationManagers;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use App\Models\Grade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class ClassSectionResource extends Resource
{
    protected static ?string $model = ClassSection::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Academic Configuration';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Class Sections';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('grade_id')
                    ->label('Grade')
                    ->options(Grade::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set, $state) =>
                        $set('code', $state ? ClassSection::generateCode(
                            Grade::find($state)?->code ?? '',
                            $set('name', '')
                        ) : '')
                    ),

                Forms\Components\Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderByDesc('is_active')->orderByDesc('start_date')->pluck('name', 'id'))
                    ->default(fn () => AcademicYear::where('is_active', true)->first()?->id)
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. A or Red')
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set, $state, $get) =>
                        $set('code', ClassSection::generateCode(
                            Grade::find($get('grade_id'))?->code ?? '',
                            $state
                        ))
                    ),

                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->disabled(),

                Forms\Components\TextInput::make('capacity')
                    ->numeric()
                    ->required()
                    ->default(40),

                Forms\Components\Select::make('class_teacher_id')
                    ->label('Class Teacher')
                    ->relationship('classTeacher', 'name')
                    ->searchable(),

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('classTeacher.name')
                    ->label('Class Teacher')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Students')
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_spaces')
                    ->label('Available Spaces')
                    ->getStateUsing(fn (ClassSection $record) => $record->getAvailableSpacesAttribute()),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade')
                    ->relationship('grade', 'name'),

                Tables\Filters\SelectFilter::make('academic_year')
                    ->relationship('academicYear', 'name'),

                Tables\Filters\Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active only')
                    ->toggle(),

                Tables\Filters\Filter::make('is_at_capacity')
                    ->query(function (Builder $query): Builder {
                        $classSections = ClassSection::all();
                        $atCapacityIds = [];

                        foreach ($classSections as $section) {
                            if ($section->isAtCapacity()) {
                                $atCapacityIds[] = $section->id;
                            }
                        }

                        return $query->whereIn('id', $atCapacityIds);
                    })
                    ->label('At Capacity')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            // RelationManagers\StudentsRelationManager::class,
            // RelationManagers\SubjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSections::route('/'),
            'create' => Pages\CreateClassSection::route('/create'),
            'view' => Pages\ViewClassSection::route('/{record}'),
            'edit' => Pages\EditClassSection::route('/{record}/edit'),
        ];
    }
}
