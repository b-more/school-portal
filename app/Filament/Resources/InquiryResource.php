<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Secretary Desk';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Inquiry::where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inquirer Information')
                    ->schema([
                        Forms\Components\TextInput::make('inquirer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('inquiry_type')
                            ->options([
                                'admission' => 'Admission',
                                'fees' => 'Fees',
                                'academic' => 'Academic',
                                'general' => 'General',
                                'other' => 'Other',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Inquiry Details')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Response')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'responded' => 'Responded',
                                'closed' => 'Closed',
                            ])
                            ->default('new')
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('response')
                            ->rows(3)
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['responded', 'closed']))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('inquirer_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('inquiry_type')
                    ->colors([
                        'success' => 'admission',
                        'warning' => 'fees',
                        'info' => 'academic',
                        'gray' => 'general',
                        'secondary' => 'other',
                    ]),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'new',
                        'info' => 'responded',
                        'gray' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('logger.name')
                    ->label('Logged By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'responded' => 'Responded',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('inquiry_type')
                    ->options([
                        'admission' => 'Admission',
                        'fees' => 'Fees',
                        'academic' => 'Academic',
                        'general' => 'General',
                        'other' => 'Other',
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'create' => Pages\CreateInquiry::route('/create'),
            'view' => Pages\ViewInquiry::route('/{record}'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['logger:id,name', 'responder:id,name']);
    }
}
