<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\TimetablePeriodResource\Pages;
use App\Models\AcademicYear;
use App\Models\TimetablePeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimetablePeriodResource extends Resource
{
    protected static ?string $model = TimetablePeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Timetable Periods';

    protected static ?string $navigationGroup = 'Timetable Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Timetable Period';

    protected static ?string $pluralModelLabel = 'Timetable Periods';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Period Details')
                    ->description('Define time slots for the school timetable')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                            ->default(fn() => AcademicYear::current()?->id)
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Period Name')
                            ->placeholder('e.g., Period 1, Assembly, Tea Break')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('short_name')
                            ->label('Short Name')
                            ->placeholder('e.g., P1, ASM, TEA')
                            ->maxLength(20)
                            ->helperText('Optional abbreviation for display in compact views'),

                        Forms\Components\Select::make('type')
                            ->label('Period Type')
                            ->options(TimetablePeriod::getTypeOptions())
                            ->default(TimetablePeriod::TYPE_LESSON)
                            ->required()
                            ->live()
                            ->helperText('Lesson periods can have subjects assigned; break periods are for assembly, tea, lunch, etc.'),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds(false)
                            ->displayFormat('H:i')
                            ->after('start_time'),

                        Forms\Components\TextInput::make('order')
                            ->label('Sequence Order')
                            ->numeric()
                            ->default(fn() => TimetablePeriod::max('order') + 1)
                            ->required()
                            ->minValue(1)
                            ->helperText('Determines the display order of periods (1 = first)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive periods will not appear in timetable management'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->placeholder('Optional notes about this period'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Period Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('short_name')
                    ->label('Short')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'lesson',
                        'warning' => 'assembly',
                        'success' => 'tea_break',
                        'info' => 'lunch_break',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn(string $state): string => TimetablePeriod::getTypeOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Duration')
                    ->suffix(' min')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Year')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('timetable_entries_count')
                    ->label('Entries')
                    ->counts('timetableEntries')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Period Type')
                    ->options(TimetablePeriod::getTypeOptions()),

                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                    ->default(fn() => AcademicYear::current()?->id),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (TimetablePeriod $record) {
                        if ($record->timetableEntries()->count() > 0) {
                            throw new \Exception('Cannot delete period that has timetable entries. Remove entries first.');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order')
            ->emptyStateHeading('No Timetable Periods')
            ->emptyStateDescription('Create periods to define time slots for your school timetable.')
            ->emptyStateIcon('heroicon-o-clock');
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
            'index' => Pages\ListTimetablePeriods::route('/'),
            'create' => Pages\CreateTimetablePeriod::route('/create'),
            'edit' => Pages\EditTimetablePeriod::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('academicYear');
    }
}
