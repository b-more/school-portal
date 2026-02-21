<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\GradingScaleResource\Pages;
use App\Models\GradingScale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradingScaleResource extends Resource
{
    protected static ?string $model = GradingScale::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Grading Scales';

    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Grading Scale Information')
                    ->description('Define the grading scale and its grade ranges')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Scale Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Primary Grading Scale'),

                        Forms\Components\Select::make('grade_level')
                            ->label('Applicable Grade Level')
                            ->options([
                                'primary' => 'Primary (Baby Class - Grade 7)',
                                'secondary' => 'Secondary (Grade 8 - Grade 12)',
                                'all' => 'All Grades',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->placeholder('Optional description for this grading scale'),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Set as Default')
                            ->helperText('Use this scale as the default for its grade level'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Grade Ranges')
                    ->description('Define the grade ranges from highest to lowest')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('grade')
                                    ->label('Grade')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('A'),

                                Forms\Components\TextInput::make('min_marks')
                                    ->label('Min %')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01),

                                Forms\Components\TextInput::make('max_marks')
                                    ->label('Max %')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01),

                                Forms\Components\TextInput::make('grade_points')
                                    ->label('Points')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(5)
                                    ->step(0.1)
                                    ->default(0),

                                Forms\Components\TextInput::make('remark')
                                    ->label('Remark')
                                    ->maxLength(50)
                                    ->placeholder('Excellent'),

                                Forms\Components\Hidden::make('sort_order')
                                    ->default(fn ($get) => 0),
                            ])
                            ->columns(5)
                            ->defaultItems(6)
                            ->reorderable()
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['grade'], $state['min_marks'], $state['max_marks'])
                                    ? "{$state['grade']}: {$state['min_marks']}% - {$state['max_marks']}%"
                                    : null
                            )
                            ->addActionLabel('Add Grade Range'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Scale Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('grade_level')
                    ->label('Grade Level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'primary' => 'success',
                        'secondary' => 'info',
                        'all' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'all' => 'All Grades',
                    }),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Grades')
                    ->counts('items')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grade_level')
                    ->options([
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'all' => 'All Grades',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGradingScales::route('/'),
            'create' => Pages\CreateGradingScale::route('/create'),
            'view' => Pages\ViewGradingScale::route('/{record}'),
            'edit' => Pages\EditGradingScale::route('/{record}/edit'),
        ];
    }
}
