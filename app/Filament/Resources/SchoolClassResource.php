<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolClassResource\Pages;
use App\Filament\Resources\SchoolClassResource\RelationManagers;
use App\Models\SchoolClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchoolClassResource extends Resource
{
    protected static ?string $model = SchoolClass::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Classes';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('department')
                    ->options([
                        'ECL' => 'ECL',
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                    ])
                    ->required(),

                Forms\Components\Select::make('grade')
                    ->label('Grade')
                    ->options([
                        'Baby Class' => 'Baby Class',
                        'Middle Class' => 'Middle Class',
                        'Reception' => 'Reception',
                        'Grade 1' => 'Grade 1',
                        'Grade 2' => 'Grade 2',
                        'Grade 3' => 'Grade 3',
                        'Grade 4' => 'Grade 4',
                        'Grade 5' => 'Grade 5',
                        'Grade 6' => 'Grade 6',
                        'Grade 7' => 'Grade 7',
                        'Grade 8' => 'Grade 8',
                        'Grade 9' => 'Grade 9',
                        'Grade 10' => 'Grade 10',
                        'Grade 11' => 'Grade 11',
                        'Grade 12' => 'Grade 12',
                    ]),

                Forms\Components\TextInput::make('section')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('department')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('section')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                // Display assigned teachers
                Tables\Columns\TextColumn::make('teachers.name')
                    ->label('Assigned Teachers')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList(),

                // Keep the count as well
                Tables\Columns\TextColumn::make('teachers_count')
                    ->counts('teachers')
                    ->label('Total Teachers'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'ECL' => 'ECL',
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // Add a view action to see details including all teachers
                Tables\Actions\ViewAction::make()
                    ->label('View Details'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->action(fn (Builder $query) => $query->update(['is_active' => true]))
                        ->icon('heroicon-o-check')
                        ->color('success'),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->action(fn (Builder $query) => $query->update(['is_active' => false]))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers for teachers and subject teachers
            RelationManagers\TeachersRelationManager::class,
            RelationManagers\SubjectTeachersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolClasses::route('/'),
            'create' => Pages\CreateSchoolClass::route('/create'),
            'edit' => Pages\EditSchoolClass::route('/{record}/edit'),
            'view' => Pages\ViewSchoolClass::route('/{record}'),
        ];
    }
}
