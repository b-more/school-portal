<?php

namespace App\Filament\Resources\TeacherResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\AcademicYear;

class SubjectTeachingsRelationManager extends RelationManager
{
    protected static string $relationship = 'subjectTeachings';

    protected static ?string $title = 'Subject Assignments';

    protected static ?string $icon = 'heroicon-o-book-open';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('academic_year_id')
                    ->label('Academic Year')
                    ->options(AcademicYear::orderByDesc('is_active')->orderByDesc('start_date')->pluck('name', 'id'))
                    ->default(AcademicYear::where('is_active', true)->first()?->id)
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('class_section_id')
                    ->label('Class Section')
                    ->options(function () {
                        return ClassSection::with('grade')
                            ->get()
                            ->mapWithKeys(function ($section) {
                                return [$section->id => "{$section->grade->name} - {$section->name}"];
                            });
                    })
                    ->required()
                    ->searchable(),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Academic Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('classSection.grade.name')
                    ->label('Grade')
                    ->sortable(),

                Tables\Columns\TextColumn::make('classSection.name')
                    ->label('Section')
                    ->sortable(),

                Tables\Columns\TextColumn::make('classSection.students_count')
                    ->label('Students')
                    ->counts(['classSection' => fn ($query) => $query->withCount('students')])
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Assigned')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Academic Year')
                    ->relationship('academicYear', 'name'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name'),

                Tables\Filters\Filter::make('current_year')
                    ->query(fn ($query) => $query->whereHas('academicYear', fn ($q) => $q->where('is_active', true)))
                    ->label('Current Year Only')
                    ->default(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Assign Subject to Teacher')
                    ->modalWidth('lg'),

                Tables\Actions\Action::make('bulk_assign')
                    ->label('Bulk Assign')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Academic Year')
                            ->options(AcademicYear::orderByDesc('is_active')->orderByDesc('start_date')->pluck('name', 'id'))
                            ->default(AcademicYear::where('is_active', true)->first()?->id)
                            ->required(),

                        Forms\Components\Select::make('subjects')
                            ->label('Subjects')
                            ->options(Subject::pluck('name', 'id'))
                            ->multiple()
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('class_sections')
                            ->label('Class Sections')
                            ->options(function () {
                                return ClassSection::with('grade')
                                    ->get()
                                    ->mapWithKeys(function ($section) {
                                        return [$section->id => "{$section->grade->name} - {$section->name}"];
                                    });
                            })
                            ->multiple()
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) {
                        $teacher = $this->getOwnerRecord();

                        foreach ($data['subjects'] as $subjectId) {
                            foreach ($data['class_sections'] as $classSectionId) {
                                $teacher->subjectTeachings()->firstOrCreate([
                                    'subject_id' => $subjectId,
                                    'class_section_id' => $classSectionId,
                                    'academic_year_id' => $data['academic_year_id'],
                                ]);
                            }
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('academic_year_id', 'desc');
    }
}
