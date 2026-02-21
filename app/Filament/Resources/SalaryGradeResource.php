<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\SalaryGradeResource\Pages;
use App\Models\SalaryGrade;
use App\Models\StaffDesignation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalaryGradeResource extends Resource
{
    protected static ?string $model = SalaryGrade::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?string $navigationLabel = 'Salary Grades';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Salary Grade Details')
                    ->description('Define salary grades for employee designations')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Grade Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('e.g., SG1, SG2, T1, T2')
                            ->helperText('Unique code for this salary grade'),

                        Forms\Components\TextInput::make('name')
                            ->label('Grade Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g., Grade 1 - Entry Level'),

                        Forms\Components\Select::make('staff_designation_id')
                            ->label('Designation')
                            ->relationship('staffDesignation', 'name')
                            ->getOptionLabelFromRecordUsing(fn (StaffDesignation $record) => "{$record->code} - {$record->name}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Select from staff designations'),

                        Forms\Components\TextInput::make('basic_salary')
                            ->label('Basic Salary')
                            ->required()
                            ->numeric()
                            ->prefix('ZMW')
                            ->minValue(0)
                            ->step(0.01),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional description of this salary grade'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive grades will not be available for selection'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Grade Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('staffDesignation.name')
                    ->label('Designation')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('basic_salary')
                    ->label('Basic Salary')
                    ->money('ZMW')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('employees_count')
                    ->label('Employees')
                    ->counts('employees')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (SalaryGrade $record) {
                        // Check if any employees are using this grade
                        if ($record->employees()->count() > 0) {
                            throw new \Exception('Cannot delete salary grade that is assigned to employees.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Salary Grades')
            ->emptyStateDescription('Create salary grades to define pay scales for different designations.')
            ->emptyStateIcon('heroicon-o-currency-dollar');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalaryGrades::route('/'),
            'create' => Pages\CreateSalaryGrade::route('/create'),
            'edit' => Pages\EditSalaryGrade::route('/{record}/edit'),
        ];
    }
}
