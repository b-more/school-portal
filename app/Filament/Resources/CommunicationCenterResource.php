<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommunicationCenterResource\Pages;
use App\Models\MessageBroadcast;
use App\Models\MessageTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class CommunicationCenterResource extends Resource
{
    protected static ?string $model = MessageBroadcast::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Communication Center';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('total_recipients')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                Forms\Components\TextInput::make('sent_count')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                Forms\Components\TextInput::make('failed_count')
                    ->numeric()
                    ->default(0)
                    ->disabled(),
                Forms\Components\TextInput::make('total_cost')
                    ->numeric()
                    ->prefix('ZMW')
                    ->default(0)
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sending' => 'Sending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('draft')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created'),
                Tables\Columns\TextColumn::make('total_recipients')
                    ->label('Recipients')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sent_count')
                    ->label('Sent')
                    ->numeric(),
                Tables\Columns\TextColumn::make('failed_count')
                    ->label('Failed')
                    ->numeric(),
                // For Filament 3, we use TextColumn with progress formatting
                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->color(fn (int $state): string => match (true) {
                        $state < 30 => 'danger',
                        $state < 70 => 'warning',
                        default => 'success',
                    }),
                // In Filament 3, BadgeColumn is replaced with TextColumn with badge()
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sending' => 'Sending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('continue')
                    ->label('Continue Sending')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->url(fn (MessageBroadcast $record): string => route('filament.admin.resources.communication-center.send-broadcast', $record))
                    ->visible(fn (MessageBroadcast $record): bool => $record->status === 'sending'),
            ])
            ->bulkActions([
                // For Filament 3, we need to properly format bulk actions
                Tables\Actions\BulkActionGroup::make([
                    // You can add bulk actions here if needed
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListMessageBroadcasts::route('/'),
            'create' => Pages\CreateBroadcast::route('/create'),
            'view' => Pages\ViewBroadcast::route('/{record}'),
            'send-broadcast' => Pages\SendBroadcast::route('/{record}/send'),
        ];
    }
}
