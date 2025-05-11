<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicYearResource\Pages;
use App\Models\AcademicYear;
use App\Models\Term;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Academic Configuration';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. 2025-2026'),

                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->default(now()->startOfYear()),

                Forms\Components\DatePicker::make('end_date')
                    ->required()
                    ->default(now()->endOfYear())
                    ->after('start_date'),

                Forms\Components\Select::make('number_of_terms')
                    ->options([
                        1 => '1 Term',
                        2 => '2 Terms',
                        3 => '3 Terms',
                        4 => '4 Terms',
                    ])
                    ->default(3)
                    ->required()
                    ->helperText('The number of terms in this academic year.'),

                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Set as active academic year')
                    ->helperText('Only one academic year can be active at a time.')
                    ->default(false),

                Forms\Components\Section::make('Terms')
                    ->description('Terms will be automatically created based on the number of terms.')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->schema([
                        Forms\Components\Placeholder::make('terms_preview')
                            ->label('Term dates will be calculated as follows:')
                            ->content(function ($get) {
                                $startDate = $get('start_date');
                                $endDate = $get('end_date');
                                $termCount = $get('number_of_terms') ?: 3;

                                if (!$startDate || !$endDate) {
                                    return 'Please select start and end dates to preview term dates.';
                                }

                                $startDate = \Carbon\Carbon::parse($startDate);
                                $endDate = \Carbon\Carbon::parse($endDate);
                                $interval = $startDate->diffInDays($endDate) / $termCount;

                                $preview = '';
                                for ($i = 1; $i <= $termCount; $i++) {
                                    $termStartDate = $startDate->copy()->addDays(($i-1) * $interval);
                                    $termEndDate = $startDate->copy()->addDays($i * $interval)->subDay();

                                    if ($i === $termCount) {
                                        $termEndDate = $endDate;
                                    }

                                    $preview .= "Term {$i}: {$termStartDate->format('d M Y')} to {$termEndDate->format('d M Y')}\n";
                                }

                                return $preview;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('number_of_terms')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('terms_count')
                    ->counts('terms')
                    ->label('Terms'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (AcademicYear $record) {
                        if ($record->is_active) {
                            Notification::make()
                                ->title('Cannot delete active academic year')
                                ->danger()
                                ->send();

                            $record->refresh();
                            return;
                        }
                    }),
                Tables\Actions\Action::make('setActive')
                    ->label('Set Active')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (AcademicYear $record) {
                        $record->update(['is_active' => true]);

                        // Also activate the first term if not already active
                        $firstTerm = $record->terms()->orderBy('start_date')->first();
                        if ($firstTerm && !$firstTerm->is_active) {
                            $firstTerm->update(['is_active' => true]);
                        }

                        Notification::make()
                            ->title('Academic year activated')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (AcademicYear $record) => !$record->is_active),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicYears::route('/'),
            'create' => Pages\CreateAcademicYear::route('/create'),
            'view' => Pages\ViewAcademicYear::route('/{record}'),
            'edit' => Pages\EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
