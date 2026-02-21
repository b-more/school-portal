<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\CallLogResource\Pages;
use App\Models\CallLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CallLogResource extends Resource
{
    protected static ?string $model = CallLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Secretary Desk';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CallLog::where('status', 'follow_up_pending')->count();

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
                Forms\Components\Section::make('Call Information')
                    ->schema([
                        Forms\Components\TextInput::make('caller_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\Select::make('call_type')
                            ->options([
                                'incoming' => 'Incoming',
                                'outgoing' => 'Outgoing',
                            ])
                            ->default('incoming')
                            ->required(),
                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->numeric()
                            ->minValue(0),
                    ])->columns(2),

                Forms\Components\Section::make('Call Details')
                    ->schema([
                        Forms\Components\TextInput::make('purpose')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Follow Up')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'logged' => 'Logged',
                                'follow_up_pending' => 'Follow Up Pending',
                                'completed' => 'Completed',
                            ])
                            ->default('logged')
                            ->required()
                            ->live(),
                        Forms\Components\Toggle::make('follow_up_required')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('status', 'follow_up_pending');
                                }
                            }),
                        Forms\Components\DatePicker::make('follow_up_date')
                            ->visible(fn (Forms\Get $get) => $get('follow_up_required'))
                            ->required(fn (Forms\Get $get) => $get('follow_up_required')),
                        Forms\Components\Textarea::make('follow_up_notes')
                            ->rows(2)
                            ->visible(fn (Forms\Get $get) => $get('follow_up_required'))
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('caller_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('call_type')
                    ->colors([
                        'info' => 'incoming',
                        'success' => 'outgoing',
                    ]),
                Tables\Columns\TextColumn::make('purpose')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\IconColumn::make('follow_up_required')
                    ->boolean()
                    ->label('Follow Up'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'logged',
                        'warning' => 'follow_up_pending',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('follow_up_date')
                    ->date('d M Y')
                    ->placeholder('-')
                    ->toggleable(),
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
                        'logged' => 'Logged',
                        'follow_up_pending' => 'Follow Up Pending',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('call_type')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ]),
                Tables\Filters\TernaryFilter::make('follow_up_required')
                    ->label('Follow Up Required'),
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
            'index' => Pages\ListCallLogs::route('/'),
            'create' => Pages\CreateCallLog::route('/create'),
            'view' => Pages\ViewCallLog::route('/{record}'),
            'edit' => Pages\EditCallLog::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['logger:id,name']);
    }
}
