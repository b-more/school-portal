<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Subject Information')
                    ->description('Basic information about the subject')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('is_code_manually_changed') && filled($state)) {
                                    $gradeLevel = $get('grade_level');
                                    $department = $get('department');

                                    if (filled($gradeLevel) && filled($department)) {
                                        $prefix = Str::upper(Str::substr($state, 0, 3));
                                        $deptPrefix = Str::substr($department, 0, 1);
                                        $code = "{$prefix}-{$deptPrefix}{$gradeLevel}";
                                        $set('code', $code);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('department')
                            ->label('Department')
                            ->options([
                                'ECL' => 'ECL',
                                'Primary' => 'Primary',
                                'Secondary' => 'Secondary',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (filled($state)) {
                                    // Adjust grade level options based on department
                                    if ($state === 'ECL') {
                                        $set('grade_level_options', [
                                            'Baby Class' => 'Baby Class',
                                            'Middle Class' => 'Middle Class',
                                            'Reception' => 'Reception',
                                        ]);
                                    } elseif ($state === 'Primary') {
                                        $set('grade_level_options', array_combine(range(1, 7),
                                            array_map(fn($num) => "Grade $num", range(1, 7))));
                                    } elseif ($state === 'Secondary') {
                                        $set('grade_level_options', array_combine(range(8, 12),
                                            array_map(fn($num) => "Grade $num", range(8, 12))));
                                    }

                                    // Also update code if possible
                                    if (!$get('is_code_manually_changed') && filled($get('name'))) {
                                        $name = $get('name');
                                        $gradeLevel = $get('grade_level');

                                        if (filled($gradeLevel)) {
                                            $prefix = Str::upper(Str::substr($name, 0, 3));
                                            $deptPrefix = Str::substr($state, 0, 1);
                                            $code = "{$prefix}-{$deptPrefix}{$gradeLevel}";
                                            $set('code', $code);
                                        }
                                    }
                                }
                            }),

                        Forms\Components\Select::make('grade_level')
                            ->label('Grade Level')
                            ->options(function (Forms\Get $get) {
                                return $get('grade_level_options') ?? [];
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                if (!$get('is_code_manually_changed') && filled($state) && filled($get('name')) && filled($get('department'))) {
                                    $name = $get('name');
                                    $department = $get('department');

                                    $prefix = Str::upper(Str::substr($name, 0, 3));
                                    $deptPrefix = Str::substr($department, 0, 1);
                                    $grade = preg_replace('/\D/', '', $state); // Extract number from grade
                                    $code = "{$prefix}-{$deptPrefix}{$grade}";
                                    $set('code', $code);
                                }
                            }),

                        Forms\Components\Hidden::make('grade_level_options'),

                        Forms\Components\Hidden::make('is_code_manually_changed')
                            ->default(false),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->helperText('Unique subject code (automatically generated, but can be modified)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('is_code_manually_changed', true)),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive subjects won\'t appear in assignments'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Curriculum Information')
                    ->description('Additional details about the subject curriculum')
                    ->schema([
                        Forms\Components\Repeater::make('topics')
                            ->label('Key Topics/Units')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Topic Name')
                                    ->required(),
                                Forms\Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->dehydrated(false), // This is just for UI, not stored in the database

                        Forms\Components\Repeater::make('assessments')
                            ->label('Assessment Types')
                            ->schema([
                                Forms\Components\TextInput::make('type')
                                    ->label('Assessment Type')
                                    ->required(),
                                Forms\Components\TextInput::make('weight')
                                    ->label('Weight (%)')
                                    ->numeric()
                                    ->suffix('%'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['type'] ?? null)
                            ->dehydrated(false), // This is just for UI, not stored in the database
                    ])
                    ->collapsible(),
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
                    ->badge()
                    ->colors([
                        'primary' => 'ECL',
                        'warning' => 'Primary',
                        'success' => 'Secondary',
                    ]),

                Tables\Columns\TextColumn::make('grade_level')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('employees_count')
                    ->counts('employees')
                    ->label('Teachers')
                    ->sortable(),

                Tables\Columns\TextColumn::make('homeworks_count')
                    ->counts('homeworks')
                    ->label('Homeworks')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'ECL' => 'ECL',
                        'Primary' => 'Primary',
                        'Secondary' => 'Secondary',
                    ]),

                Tables\Filters\SelectFilter::make('grade_level')
                    ->options(function () {
                        $options = [];

                        // ECL options
                        $eclOptions = [
                            'Baby Class' => 'Baby Class',
                            'Middle Class' => 'Middle Class',
                            'Reception' => 'Reception',
                        ];

                        // Primary options (Grades 1-7)
                        $primaryOptions = array_combine(
                            array_map(fn($num) => "Grade $num", range(1, 7)),
                            array_map(fn($num) => "Grade $num", range(1, 7))
                        );

                        // Secondary options (Grades 8-12)
                        $secondaryOptions = array_combine(
                            array_map(fn($num) => "Grade $num", range(8, 12)),
                            array_map(fn($num) => "Grade $num", range(8, 12))
                        );

                        return array_merge($eclOptions, $primaryOptions, $secondaryOptions);
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('clone')
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Subject $record) {
                        $clone = $record->replicate();
                        $clone->name = "{$record->name} (Copy)";
                        $clone->code = "{$record->code}-COPY-" . rand(100, 999);
                        $clone->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Builder $query) {
                            $query->update(['is_active' => true]);
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Mark as Inactive')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function (Builder $query) {
                            $query->update(['is_active' => false]);
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EmployeesRelationManager::class,
            RelationManagers\HomeworksRelationManager::class,
            RelationManagers\ResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
