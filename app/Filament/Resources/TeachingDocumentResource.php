<?php

namespace App\Filament\Resources;

use App\Constants\RoleConstants;
use App\Filament\Resources\TeachingDocumentResource\Pages;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeachingDocument;
use App\Models\Term;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TeachingDocumentResource extends Resource
{
    protected static ?string $model = TeachingDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Academic Management';
    protected static ?string $navigationLabel = 'Teaching Documents';
    protected static ?string $slug = 'teaching-documents';
    protected static ?int $navigationSort = 9;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                TeachingDocument::query()
                    ->with(['teacher', 'subject', 'classSection.grade', 'term', 'academicYear'])
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Teacher')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => TeachingDocument::DOCUMENT_TYPES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'scheme_of_work' => 'success',
                        'lesson_plan' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable(),
                Tables\Columns\TextColumn::make('classSection')
                    ->label('Class')
                    ->formatStateUsing(function ($record) {
                        $cs = $record->classSection;
                        return $cs ? ($cs->grade->name . ' - ' . $cs->name) : '-';
                    }),
                Tables\Columns\TextColumn::make('term.name')
                    ->label('Term'),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Year'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Type')
                    ->options(TeachingDocument::DOCUMENT_TYPES),
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(
                        Teacher::whereHas('teachingDocuments')
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable(),
                Tables\Filters\SelectFilter::make('term_id')
                    ->label('Term')
                    ->options(function () {
                        $year = AcademicYear::current();
                        if (!$year) return [];
                        return Term::where('academic_year_id', $year->id)
                            ->pluck('name', 'id')
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (TeachingDocument $record) => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (TeachingDocument $record) {
                        if ($record->file_path) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Document Details')
                    ->schema([
                        TextEntry::make('teacher.name')
                            ->label('Teacher'),
                        TextEntry::make('document_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => TeachingDocument::DOCUMENT_TYPES[$state] ?? $state),
                        TextEntry::make('title'),
                        TextEntry::make('subject.name')
                            ->label('Subject'),
                        TextEntry::make('classSection')
                            ->label('Class')
                            ->formatStateUsing(function ($record) {
                                $cs = $record->classSection;
                                return $cs ? ($cs->grade->name . ' - ' . $cs->name) : '-';
                            }),
                        TextEntry::make('academicYear.name')
                            ->label('Academic Year'),
                        TextEntry::make('term.name')
                            ->label('Term'),
                        TextEntry::make('original_filename')
                            ->label('File'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Uploaded')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachingDocuments::route('/'),
            'view' => Pages\ViewTeachingDocument::route('/{record}'),
        ];
    }
}
