<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolSettingsResource\Pages;
use App\Filament\Resources\SchoolSettingsResource\RelationManagers;
use App\Models\SchoolSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class SchoolSettingsResource extends Resource
{
    protected static ?string $model = SchoolSettings::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'System Configuration';
    protected static ?string $navigationLabel = 'School Settings';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\TextInput::make('school_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('school_code')
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('school_motto')
                                    ->maxLength(255),
                                Forms\Components\FileUpload::make('school_logo')
                                    ->image()
                                    ->directory('school-logos')
                                    ->visibility('public')
                                    ->maxSize(2048),
                            ]),

                        Forms\Components\Tabs\Tab::make('Contact Information')
                            ->schema([
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('city')
                                            ->maxLength(100),
                                        Forms\Components\TextInput::make('state_province')
                                            ->maxLength(100),
                                    ])->columns(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('country')
                                            ->maxLength(100)
                                            ->default('Zambia'),
                                        Forms\Components\TextInput::make('postal_code')
                                            ->maxLength(20),
                                    ])->columns(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('alternate_phone')
                                            ->tel()
                                            ->maxLength(20),
                                    ])->columns(2),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('website')
                                            ->url()
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('System Configuration')
                            ->schema([
                                Forms\Components\Select::make('currency_code')
                                    ->options([
                                        'ZMW' => 'Zambian Kwacha (ZMW)',
                                        'USD' => 'US Dollar (USD)',
                                        'GBP' => 'British Pound (GBP)',
                                        'EUR' => 'Euro (EUR)',
                                    ])
                                    ->default('ZMW')
                                    ->required(),
                                Forms\Components\Select::make('timezone')
                                    ->options([
                                        'Africa/Lusaka' => 'Lusaka (GMT+2)',
                                        'UTC' => 'UTC',
                                        'Africa/Johannesburg' => 'Johannesburg (GMT+2)',
                                        'Africa/Nairobi' => 'Nairobi (GMT+3)',
                                    ])
                                    ->default('Africa/Lusaka')
                                    ->required(),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('school_head_name')
                                            ->label('Head Teacher/Principal Name')
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('school_head_title')
                                            ->label('Head Teacher/Principal Title')
                                            ->maxLength(100)
                                            ->default('Principal'),
                                    ])->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchoolSettings::route('/'),
        ];
    }
}
