<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GetInTouchResource\Pages;
use App\Models\GetInTouch;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\View;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;

class GetInTouchResource extends Resource
{
    protected static ?string $model = GetInTouch::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(1)->schema([
                    Section::make('Contact Information')
                        ->description('Contact details of the person')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        ]),

                    Section::make('Message Details')
                        ->description('Message content and status')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->schema([
                            Grid::make(1)->schema([
                                Textarea::make('message')
                                    ->required()
                                    ->maxLength(65535)
                                    ->rows(4),

                                Select::make('is_read')
                                    ->label('Status')
                                    ->options([
                                        '0' => 'Unread',
                                        '1' => 'Read'
                                    ])
                                    ->required()
                                    ->reactive(),
                            ]),
                        ]),

                    Section::make('Response')
                        ->description('Send a response message')
                        ->icon('heroicon-m-paper-airplane')
                        ->schema([
                            Grid::make(1)->schema([
                                Textarea::make('response_message')
                                    ->label('Response Message')
                                    ->rows(3)
                                    ->helperText('This message will be sent via SMS to the contact')
                                    ->required()
                                    ->default('Thank you for contacting His Kingdom Church. We have received your message and will get back to you soon.'),
                            ]),
                        ])->visible(fn (callable $get) => $get('is_read') === '1'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
    ->columns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable()
            ->weight(FontWeight::Bold)
            ->description(fn (GetInTouch $record): string => $record->email),

        Tables\Columns\TextColumn::make('phone')
            ->searchable()
            ->icon('heroicon-m-phone'),

        Tables\Columns\TextColumn::make('message')
            ->limit(50)
            ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                $state = $column->getState();
                return strlen($state) > 50 ? $state : null;
            }),

        Tables\Columns\TextColumn::make('is_read')
            ->label('Status')
            ->formatStateUsing(fn (bool $state): string => $state ? 'Read' : 'Unread')
            ->badge()
            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

        Tables\Columns\TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(),
    ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('is_read')
                    ->label('Status')
                    ->options([
                        '0' => 'Unread',
                        '1' => 'Read'
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
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
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalContent(fn (GetInTouch $record) => view(
                        'filament.resources.get-in-touch.view',
                        ['record' => $record]
                    )),

                Tables\Actions\Action::make('sendResponse')
                    ->label('Send Response')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Textarea::make('response_message')
                            ->label('Response Message')
                            ->required()
                            ->default('Thank you for contacting His Kingdom Church. We have received your message and will get back to you soon.')
                            ->rows(3),
                    ])
                    ->action(function (GetInTouch $record, array $data): void {
                        try {
                            // Send SMS
                            SmsService::send($data['response_message'], $record->phone);

                            // Update status to read
                            $record->update(['is_read' => true]);

                            // Log the action
                            Log::info('Response sent to contact', [
                                'contact_id' => $record->id,
                                'sent_by' => auth()->id()
                            ]);

                            // Show success notification
                            Notification::make()
                                ->title('Response sent successfully')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Failed to send response', [
                                'contact_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);
                            throw $e;
                        }
                    })
                    ->visible(fn (GetInTouch $record) => !$record->is_read)
                    ->modalWidth('lg'),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsRead')
                        ->label('Mark as Read')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (!$record->is_read) {
                                    try {
                                        $record->update(['is_read' => true]);

                                        $message = "Thank you for contacting His Kingdom Church. We have received your message and will get back to you soon.";
                                        SmsService::send($message, $record->phone);

                                        Log::info('Message marked as read (bulk)', [
                                            'contact_id' => $record->id,
                                            'updated_by' => auth()->id()
                                        ]);
                                    } catch (\Exception $e) {
                                        Log::error('Failed to update message status (bulk)', [
                                            'contact_id' => $record->id,
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                }
                            });
                        })
                        ->requiresConfirmation()
                ]),
            ])
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('No messages yet')
            ->emptyStateDescription('When someone contacts you through the contact form, their messages will appear here.')
            ->poll();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Contact Information')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('Name'),
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email'),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Phone'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Message')
                    ->schema([
                        Infolists\Components\TextEntry::make('message')
                            ->label('Message Content')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Status')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('is_read')
                                    ->label('Status')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Read' : 'Unread')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Received At')
                                    ->dateTime(),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
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
            'index' => Pages\ListGetInTouches::route('/'),
            'create' => Pages\CreateGetInTouch::route('/create'),
            'edit' => Pages\EditGetInTouch::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_read', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_read', false)->count() > 0
            ? 'warning'
            : 'primary';
    }
}
