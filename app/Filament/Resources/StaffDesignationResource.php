<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\StaffDesignationResource\Pages;
use App\Models\StaffDesignation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StaffDesignationResource extends Resource
{
    protected static ?string $model = StaffDesignation::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?string $navigationLabel = 'Staff Designations';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Designation Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Senior Teacher'),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->helperText('Unique identifier (e.g., senior_teacher)')
                            ->placeholder('e.g., senior_teacher'),

                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->placeholder('Description of this designation'),

                        Forms\Components\Select::make('section')
                            ->options([
                                'primary' => 'Primary Only',
                                'secondary' => 'Secondary Only',
                                'both' => 'Both Sections',
                            ])
                            ->default('both')
                            ->required(),

                        Forms\Components\TextInput::make('hierarchy_level')
                            ->numeric()
                            ->default(5)
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('1 = Highest, 10 = Lowest')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order (lower = first)'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->options([
                                'view_all_students' => 'View All Students',
                                'view_section_students' => 'View Section Students',
                                'view_all_teachers' => 'View All Teachers',
                                'view_section_teachers' => 'View Section Teachers',
                                'manage_homework' => 'Manage Homework',
                                'manage_results' => 'Manage Results',
                                'view_all_reports' => 'View All Reports',
                                'view_section_reports' => 'View Section Reports',
                                'manage_curriculum' => 'Manage Curriculum',
                                'mentor_teachers' => 'Mentor Teachers',
                                'approve_leave' => 'Approve Leave Requests',
                            ])
                            ->columns(3),
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

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('section')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'primary' => 'success',
                        'secondary' => 'info',
                        'both' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('hierarchy_level')
                    ->label('Level')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 1 => 'danger',
                        $state <= 2 => 'warning',
                        $state <= 3 => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('teachers_count')
                    ->label('Teachers')
                    ->counts('teachers')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('section')
                    ->options([
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                        'both' => 'Both',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hierarchy_level')
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffDesignations::route('/'),
            'create' => Pages\CreateStaffDesignation::route('/create'),
            'edit' => Pages\EditStaffDesignation::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->role_id === RoleConstants::ADMIN;
    }
}
