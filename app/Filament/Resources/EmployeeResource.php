<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Staff Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

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
                Forms\Components\Tabs::make('Employee')
                    ->tabs([
                        // Personal Information Tab
                        Forms\Components\Tabs\Tab::make('Personal Info')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Basic Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->required()
                                            ->placeholder('260xxxxxxxxx')
                                            ->maxLength(20),
                                        Forms\Components\DatePicker::make('date_of_birth')
                                            ->maxDate(now()->subYears(18)),
                                        Forms\Components\Select::make('gender')
                                            ->options([
                                                'male' => 'Male',
                                                'female' => 'Female',
                                            ]),
                                        Forms\Components\Select::make('marital_status')
                                            ->options([
                                                'single' => 'Single',
                                                'married' => 'Married',
                                                'divorced' => 'Divorced',
                                                'widowed' => 'Widowed',
                                            ]),
                                        Forms\Components\TextInput::make('nationality')
                                            ->default('Zambian')
                                            ->maxLength(100),
                                        Forms\Components\FileUpload::make('profile_photo')
                                            ->image()
                                            ->directory('employee-photos')
                                            ->maxSize(2048)
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Address')
                                    ->schema([
                                        Forms\Components\Textarea::make('address')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(100),
                                        Forms\Components\Select::make('province')
                                            ->options([
                                                'lusaka' => 'Lusaka',
                                                'copperbelt' => 'Copperbelt',
                                                'central' => 'Central',
                                                'eastern' => 'Eastern',
                                                'luapula' => 'Luapula',
                                                'muchinga' => 'Muchinga',
                                                'northern' => 'Northern',
                                                'north_western' => 'North Western',
                                                'southern' => 'Southern',
                                                'western' => 'Western',
                                            ]),
                                    ])->columns(2),
                            ]),

                        // Statutory Compliance Tab
                        Forms\Components\Tabs\Tab::make('Statutory Info')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Section::make('National IDs & Compliance')
                                    ->description('Statutory requirements for Zambia')
                                    ->schema([
                                        Forms\Components\TextInput::make('nrc_number')
                                            ->label('NRC Number')
                                            ->placeholder('123456/12/1')
                                            ->maxLength(50)
                                            ->helperText('National Registration Card Number'),
                                        Forms\Components\TextInput::make('napsa_number')
                                            ->label('NAPSA Number')
                                            ->maxLength(50)
                                            ->helperText('National Pension Scheme Authority')
                                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                                        Forms\Components\TextInput::make('tpin_number')
                                            ->label('TPIN')
                                            ->maxLength(50)
                                            ->helperText('Taxpayer Identification Number (ZRA)')
                                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                                        Forms\Components\TextInput::make('nhima_number')
                                            ->label('NHIMA Number')
                                            ->maxLength(50)
                                            ->helperText('National Health Insurance Number')
                                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                                    ])->columns(2),
                            ]),

                        // Employment Details Tab
                        Forms\Components\Tabs\Tab::make('Employment')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Section::make('Employment Details')
                                    ->schema([
                                        Forms\Components\TextInput::make('employee_id')
                                            ->label('Employee ID')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(50),
                                        Forms\Components\Select::make('role_id')
                                            ->relationship('role', 'name')
                                            ->required()
                                            ->live()
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
                                        Forms\Components\Select::make('salary_grade_id')
                                            ->label('Salary Grade')
                                            ->relationship('salaryGrade', 'name')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->designation_name} (ZMW " . number_format($record->basic_salary, 2) . ")")
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state) {
                                                    $salaryGrade = \App\Models\SalaryGrade::with('staffDesignation')->find($state);
                                                    if ($salaryGrade) {
                                                        $set('basic_salary', $salaryGrade->basic_salary);
                                                        $set('position', $salaryGrade->designation_name);
                                                    }
                                                }
                                            })
                                            ->helperText('Select salary grade to auto-fill basic salary')
                                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                                        Forms\Components\TextInput::make('basic_salary')
                                            ->label('Basic Salary')
                                            ->numeric()
                                            ->prefix('ZMW')
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Derived from salary grade')
                                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                                        Forms\Components\Select::make('employment_type')
                                            ->options([
                                                'permanent' => 'Permanent',
                                                'contract' => 'Contract',
                                                'part_time' => 'Part Time',
                                                'probation' => 'Probation',
                                            ])
                                            ->default('permanent')
                                            ->required(),
                                        Forms\Components\Select::make('status')
                                            ->options([
                                                'active' => 'Active',
                                                'inactive' => 'Inactive',
                                            ])
                                            ->default('active')
                                            ->required(),
                                    ])->columns(2),

                                Forms\Components\Section::make('Contract Dates')
                                    ->schema([
                                        Forms\Components\DatePicker::make('joining_date')
                                            ->required(),
                                        Forms\Components\DatePicker::make('contract_start_date'),
                                        Forms\Components\DatePicker::make('contract_end_date'),
                                        Forms\Components\DatePicker::make('probation_end_date'),
                                        Forms\Components\DatePicker::make('confirmation_date')
                                            ->helperText('Date employee was confirmed after probation'),
                                        Forms\Components\DatePicker::make('designation_changed_date')
                                            ->label('Designation Changed Date')
                                            ->helperText('Date when current designation/salary grade was assigned'),
                                    ])->columns(3),

                                // Teacher-specific fields
                                Forms\Components\Section::make('Teacher Assignment')
                                    ->schema([
                                        Forms\Components\Select::make('grade_id')
                                            ->label('Assigned Grade')
                                            ->options(Grade::pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->live(),
                                        Forms\Components\Select::make('class_section_id')
                                            ->label('Class Section')
                                            ->options(function (Get $get) {
                                                $gradeId = $get('grade_id');
                                                if (!$gradeId) return [];
                                                return ClassSection::where('grade_id', $gradeId)->pluck('name', 'id');
                                            })
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\Toggle::make('is_class_teacher')
                                            ->label('Is Class Teacher?'),
                                        Forms\Components\Toggle::make('is_grade_teacher')
                                            ->label('Is Grade Teacher?'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get): bool => $get('role_id') == RoleConstants::TEACHER),
                            ]),

                        // Qualifications Tab
                        Forms\Components\Tabs\Tab::make('Qualifications')
                            ->icon('heroicon-o-academic-cap')
                            ->schema([
                                Forms\Components\Section::make('Educational Background')
                                    ->schema([
                                        Forms\Components\TextInput::make('highest_qualification')
                                            ->maxLength(255)
                                            ->placeholder('e.g., Bachelor of Education'),
                                        Forms\Components\TextInput::make('qualification_institution')
                                            ->maxLength(255)
                                            ->placeholder('e.g., University of Zambia'),
                                        Forms\Components\TextInput::make('qualification_year')
                                            ->numeric()
                                            ->minValue(1950)
                                            ->maxValue(date('Y')),
                                        Forms\Components\Textarea::make('professional_certifications')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->placeholder('List any professional certifications'),
                                    ])->columns(3),
                            ]),

                        // Emergency & Next of Kin Tab
                        Forms\Components\Tabs\Tab::make('Emergency Contact')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Emergency Contact')
                                    ->schema([
                                        Forms\Components\TextInput::make('emergency_contact_name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('emergency_contact_phone')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\Select::make('emergency_contact_relationship')
                                            ->options([
                                                'spouse' => 'Spouse',
                                                'parent' => 'Parent',
                                                'sibling' => 'Sibling',
                                                'child' => 'Child',
                                                'friend' => 'Friend',
                                                'other' => 'Other',
                                            ]),
                                    ])->columns(3),

                                Forms\Components\Section::make('Next of Kin')
                                    ->schema([
                                        Forms\Components\TextInput::make('next_of_kin_name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('next_of_kin_phone')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\Select::make('next_of_kin_relationship')
                                            ->options([
                                                'spouse' => 'Spouse',
                                                'parent' => 'Parent',
                                                'sibling' => 'Sibling',
                                                'child' => 'Child',
                                                'friend' => 'Friend',
                                                'other' => 'Other',
                                            ]),
                                        Forms\Components\Textarea::make('next_of_kin_address')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])->columns(3),
                            ]),

                        // Bank Details Tab
                        Forms\Components\Tabs\Tab::make('Bank Details')
                            ->icon('heroicon-o-building-library')
                            ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN)
                            ->schema([
                                Forms\Components\Section::make('Bank Account Information')
                                    ->description('For salary payments')
                                    ->schema([
                                        Forms\Components\TextInput::make('bank_name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('bank_branch')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('bank_account_number')
                                            ->maxLength(50),
                                        Forms\Components\TextInput::make('bank_account_name')
                                            ->maxLength(255)
                                            ->helperText('Name as it appears on account'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('employee_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('department')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ecl' => 'info',
                        'primary' => 'success',
                        'secondary' => 'warning',
                        'administration' => 'danger',
                        'support' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('role.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn ($record): string => match ($record->role_id) {
                        RoleConstants::TEACHER => 'success',
                        RoleConstants::ADMIN => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('employment_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'permanent' => 'success',
                        'contract' => 'warning',
                        'part_time' => 'info',
                        'probation' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('joining_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('years_of_service')
                    ->label('Service')
                    ->suffix(' yrs')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
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
                Tables\Filters\SelectFilter::make('employment_type')
                    ->options([
                        'permanent' => 'Permanent',
                        'contract' => 'Contract',
                        'part_time' => 'Part Time',
                        'probation' => 'Probation',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->role_id === RoleConstants::ADMIN),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LeaveApplicationsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['role:id,name']);
    }
}
