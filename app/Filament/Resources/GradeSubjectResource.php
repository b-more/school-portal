<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeSubjectResource\Pages;
use App\Filament\Resources\GradeSubjectResource\RelationManagers;
use App\Models\GradeSubject;
use App\Models\Grade;
use App\Models\Subject;
use App\Constants\RoleConstants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GradeSubjectResource extends Resource
{
    protected static ?string $model = GradeSubject::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Academic Management';

    protected static ?string $navigationLabel = 'Grade Subjects';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::SCHOOL_SECRETARY]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Grade Subject Assignment')
                    ->description('Assign subjects to grades and specify if they are mandatory')
                    ->schema([
                        Forms\Components\Select::make('grade_id')
                            ->label('Grade')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('is_mandatory')
                            ->label('Mandatory Subject')
                            ->helperText('Is this subject mandatory for this grade?')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grade.name')
                    ->label('Grade')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject.code')
                    ->label('Subject Code')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_mandatory')
                    ->label('Mandatory')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('teachers_display')
                    ->label('Teachers Assigned')
                    ->getStateUsing(function ($record) {
                        $teachers = $record->teachers;

                        if ($teachers->isEmpty()) {
                            return '—';
                        }

                        return $teachers->map(function ($teacher) {
                            return $teacher->name . ' (' . $teacher->class_section_name . ')';
                        })->join(', ');
                    })
                    ->wrap()
                    ->searchable(false)
                    ->sortable(false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('grade.name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('grade_id')
                    ->label('Grade')
                    ->relationship('grade', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_mandatory')
                    ->label('Mandatory Subjects')
                    ->placeholder('All subjects')
                    ->trueLabel('Mandatory only')
                    ->falseLabel('Optional only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('setMandatory')
                        ->label('Mark as Mandatory')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_mandatory' => true]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('setOptional')
                        ->label('Mark as Optional')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_mandatory' => false]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListGradeSubjects::route('/'),
            'create' => Pages\CreateGradeSubject::route('/create'),
            'edit' => Pages\EditGradeSubject::route('/{record}/edit'),
        ];
    }
}
