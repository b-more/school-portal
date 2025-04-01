<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserCredentialResource\Pages;
use App\Models\UserCredential;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class UserCredentialResource extends Resource
{
    protected static ?string $model = UserCredential::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Access Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'username';

    // public static function shouldRegisterNavigation(): bool
    // {
    //     // Only show to users with appropriate permissions
    //     //return auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin');
    // }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),

                Forms\Components\TextInput::make('username')
                    ->disabled()
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->disabled()
                    ->required(),

                Forms\Components\Toggle::make('is_sent')
                    ->disabled()
                    ->required(),

                Forms\Components\DateTimePicker::make('sent_at')
                    ->disabled(),

                Forms\Components\TextInput::make('delivery_method')
                    ->disabled()
                    ->required(),

                Forms\Components\Toggle::make('is_retrieved')
                    ->disabled()
                    ->required(),

                Forms\Components\DateTimePicker::make('retrieved_at')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('expires_at')
                    ->disabled()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sent')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sms' => 'success',
                        'email' => 'info',
                        'manual' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_retrieved')
                    ->boolean(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => now()->gt($record->expires_at) ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('delivery_method')
                    ->options([
                        'sms' => 'SMS',
                        'email' => 'Email',
                        'manual' => 'Manual',
                    ]),

                Tables\Filters\TernaryFilter::make('is_sent')
                    ->label('Sent Status'),

                Tables\Filters\TernaryFilter::make('is_retrieved')
                    ->label('Retrieved Status'),

                Tables\Filters\Filter::make('expired')
                    ->query(function (Builder $query) {
                        return $query->where('expires_at', '<', now());
                    })
                    ->label('Expired Credentials')
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('resendSms')
                    ->label('Resend via SMS')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->default(fn ($record) => $record->user->phone ?? '')
                            ->required(),
                    ])
                    ->action(function (UserCredential $record, array $data) {
                        try {
                            $message = "Hello {$record->user->name}, your account credentials: Username: {$record->username}, Password: {$record->password}. Please log in and change your password.";

                            SmsService::send($message, $data['phone']);

                            // Update the credential record
                            $record->update([
                                'is_sent' => true,
                                'sent_at' => now(),
                                'delivery_method' => 'sms',
                            ]);

                            // Log success
                            Log::info('Credentials resent via SMS', [
                                'user_id' => $record->user_id,
                                'username' => $record->username,
                                'phone' => $data['phone'],
                            ]);

                            Notification::make()
                                ->title('Credentials Sent')
                                ->body("Login credentials successfully sent to {$data['phone']}")
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Failed to resend credentials via SMS', [
                                'user_id' => $record->user_id,
                                'username' => $record->username,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Error')
                                ->body("Failed to send credentials: {$e->getMessage()}")
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => !$record->is_sent || $record->delivery_method === 'manual'),

                Tables\Actions\Action::make('markRetrieved')
                    ->label('Mark Retrieved')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (UserCredential $record) {
                        $record->update([
                            'is_retrieved' => true,
                            'retrieved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Credentials Retrieved')
                            ->body("Credentials marked as retrieved")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => !$record->is_retrieved),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                        //->visible(fn () => auth()->user()->hasRole('super_admin')),

                    Tables\Actions\BulkAction::make('markRetrievedBulk')
                        ->label('Mark Retrieved')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Builder $query) {
                            $query->update([
                                'is_retrieved' => true,
                                'retrieved_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Credentials Retrieved')
                                ->body("Selected credentials marked as retrieved")
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListUserCredentials::route('/'),
            'view' => Pages\ViewUserCredential::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_sent', false)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('is_sent', false)->count() > 0
            ? 'warning'
            : 'primary';
    }
}
