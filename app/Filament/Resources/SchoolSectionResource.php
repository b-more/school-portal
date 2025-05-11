<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolSectionResource\Pages;
use App\Filament\Resources\SchoolSectionResource\RelationManagers;
use App\Models\SchoolSection;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Auth;

class SchoolSectionResource extends Resource
{
    protected static ?string $model = SchoolSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Academic Configuration';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'School Sections';

    protected static ?string $modelLabel = 'School Section';

    protected static ?string $pluralModelLabel = 'School Sections';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(SchoolSection::class, 'name', ignoreRecord: true)
                            ->placeholder('e.g., Primary School, High School'),

                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(10)
                            ->unique(SchoolSection::class, 'code', ignoreRecord: true)
                            ->placeholder('e.g., PRIM, HIGH')
                            ->rules(['alpha_dash'])
                            ->hint('Use letters, numbers, dashes and underscores only'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Brief description of the school section...'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Management')
                    ->schema([
                        Forms\Components\Select::make('head_of_section_id')
                            ->label('Head of Section')
                            ->relationship('headOfSection', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Select an employee to manage this section')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required(),
                                // Add other employee fields as needed
                            ]),

                        Forms\Components\TextInput::make('order')
                            ->label('Display Order')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Order in which sections are displayed (lower numbers appear first)'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive sections will not be visible to students and staff'),
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
                    ->alignCenter()
                    ->width(50),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('headOfSection.name')
                    ->label('Head of Section')
                    ->searchable()
                    ->sortable()
                    ->default('-')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('grades_count')
                    ->label('Grades')
                    ->counts('grades')
                    ->badge()
                    ->color('success')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('head_of_section_id')
                    ->label('Head of Section')
                    ->relationship('headOfSection', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All Sections')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleStatus')
                    ->label(fn (SchoolSection $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (SchoolSection $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (SchoolSection $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (SchoolSection $record) {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Section Activated' : 'Section Deactivated')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            $count = $records->update(['is_active' => true]);

                            Notification::make()
                                ->title("$count section(s) activated")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('danger')
                        ->action(function ($records) {
                            $count = $records->update(['is_active' => false]);

                            Notification::make()
                                ->title("$count section(s) deactivated")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolSections::route('/'),
            'create' => Pages\CreateSchoolSection::route('/create'),
            'view' => Pages\ViewSchoolSection::route('/{record}'),
            'edit' => Pages\EditSchoolSection::route('/{record}/edit'),
        ];
    }

    // Add a global search configuration
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code' => $record->code,
            'Head of Section' => $record->headOfSection?->name,
            'Grades' => $record->grades_count,
        ];
    }

    // Add search summary
    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Tables\Actions\ViewAction::make('view')
                ->url(static::getUrl('view', ['record' => $record])),
            Tables\Actions\EditAction::make('edit')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }
}
