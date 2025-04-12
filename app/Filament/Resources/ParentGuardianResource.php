<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParentGuardianResource\Pages;
use App\Models\ParentGuardian;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ParentGuardianResource extends Resource
{
    protected static ?string $model = ParentGuardian::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Student Management';

    protected static ?string $navigationLabel = 'Parents & Guardians';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->description('Basic contact and identification details')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter full name'),

                        Forms\Components\Select::make('relationship')
                            ->label('Relationship to Student')
                            ->options([
                                'father' => 'Father',
                                'mother' => 'Mother',
                                'guardian' => 'Guardian',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('nrc')
                            ->label('NRC Number')
                            ->maxLength(255)
                            ->placeholder('e.g. 123456/78/9')
                            ->helperText('National Registration Card number'),

                        Forms\Components\TextInput::make('nationality')
                            ->maxLength(255)
                            ->placeholder('e.g. Zambian')
                            ->default('Zambian'),

                        Forms\Components\TextInput::make('occupation')
                            ->maxLength(255)
                            ->placeholder('Enter occupation'),
                    ]),

                Forms\Components\Section::make('Contact Details')
                    ->description('How to reach the parent/guardian')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([


                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('e.g. 260972266217')
                            ->helperText('Primary contact number'),

                        Forms\Components\TextInput::make('alternate_phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('e.g. 260972266218')
                            ->helperText('Alternative contact number (optional)'),

                            Forms\Components\TextInput::make('email')
                            ->email()

                            ->maxLength(255)
                            ->placeholder('email@example.com'),

                        Forms\Components\Textarea::make('address')
                            ->required()
                            ->rows(3)
                            ->placeholder('Enter physical address')
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Portal Access')
                    ->description('System access information')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Placeholder::make('access_info')
                            ->content('A user account will be automatically created for this parent/guardian when you save this form. They will receive their login credentials via SMS.'),
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nrc')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('relationship')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'father' => 'info',
                        'mother' => 'success',
                        'guardian' => 'warning',
                        'other' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('nationality')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('occupation')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Children')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('relationship')
                    ->options([
                        'father' => 'Father',
                        'mother' => 'Mother',
                        'guardian' => 'Guardian',
                        'other' => 'Other',
                    ]),

                Tables\Filters\Filter::make('has_children')
                    ->label('Has Children')
                    ->query(fn (Builder $query): Builder => $query->has('students'))
                    ->toggle(),

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
                Tables\Actions\ViewAction::make()
                    ->color('info'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('sendSms')
                    ->label('Send SMS')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->required()
                            ->default('Dear parent, this is an important message regarding your child.')
                            ->rows(3),
                    ])
                    ->action(function (ParentGuardian $record, array $data) {
                        // Logic for sending SMS would go here
                        // You can reuse the SMS service logic from other resources
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulkSms')
                        ->label('Send Bulk SMS')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('warning')
                        ->form([
                            Forms\Components\Textarea::make('message')
                                ->required()
                                ->default('Dear parents, this is an important announcement from the school.')
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data) {
                            // Logic for sending bulk SMS would go here
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ParentGuardianResource\RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParentGuardians::route('/'),
            'create' => Pages\CreateParentGuardian::route('/create'),
            'view' => Pages\ViewParentGuardian::route('/{record}'),
            'edit' => Pages\EditParentGuardian::route('/{record}/edit'),
        ];
    }
}
