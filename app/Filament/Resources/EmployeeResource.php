<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Staff Management';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                           // ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->placeholder("260xxxxxxxx")
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('profile_photo')
                            ->image()
                            ->directory('employee-photos')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\TextInput::make('employee_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('role_id')  // Changed from 'role' to 'role_id'
                            ->relationship('role', 'name')  // Use relationship to get role names
                            ->required()
                            //->searchable()
                            ->preload(),
                        Forms\Components\Select::make('department')
                            ->options([
                                'ecl' => 'ECL',
                                'primary' => 'Primary School',
                                'secondary' => 'Secondary School',
                                'administration' => 'Administration',
                                'support' => 'Support Staff',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('position')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('joining_date')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\TextInput::make('basic_salary')
                            ->numeric()
                            ->prefix('ZMW'),
                            //->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role.name')  // Changed to use relationship
                    ->label('Role')
                    ->badge()
                    ->color(fn ($record): string => match ($record->role_id) {
                        RoleConstants::TEACHER => 'success',
                        RoleConstants::ADMIN => 'danger',
                        RoleConstants::SUPPORT => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('joining_date')
                    ->date(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('ZMW')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')  // Changed from 'role' to 'role_id'
                    ->label('Role')
                    ->relationship('role', 'name')
                    ->multiple(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'ecl' => 'ECL',
                        'primary' => 'Primary School',
                        'secondary' => 'Secondary School',
                        'administration' => 'Administration',
                        'support' => 'Support Staff',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            // EmployeeResource\RelationManagers\PayrollsRelationManager::class,
            // EmployeeResource\RelationManagers\SubjectsRelationManager::class,
            // EmployeeResource\RelationManagers\HomeworksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            //'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
