<?php

namespace App\Filament\Resources\SchoolClassResource\RelationManagers;

use App\Models\Subject;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SubjectTeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'subjectTeachers';
    protected static ?string $title = 'Subject-Teacher Assignments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return Teacher::where('is_active', true)
                            ->whereNotNull('specialization') // Secondary teachers only
                            ->get()
                            ->mapWithKeys(function ($teacher) {
                                return [$teacher->id => "{$teacher->name} ({$teacher->employee_id}) - {$teacher->specialization}"];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Only secondary teachers with specializations are shown'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Teacher Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee_id')
                    ->label('Employee ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Specialization')
                    ->searchable(),

                // Get the subject name from the pivot relationship
                Tables\Columns\TextColumn::make('subject_name')
                    ->label('Subject')
                    ->getStateUsing(function ($record): string {
                        // Get subject from the pivot table
                        $subjectId = DB::table('class_subject_teacher')
                            ->where('class_id', $this->ownerRecord->id)
                            ->where('teacher_id', $record->id)
                            ->value('subject_id');

                        return Subject::find($subjectId)?->name ?? 'Unknown';
                    }),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                // Filter by subject
                Tables\Filters\Filter::make('subject')
                    ->form([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['subject_id'])) {
                            return $query;
                        }

                        return $query->whereHas('subjects', function ($q) use ($data) {
                            $q->where('class_subject_teacher.subject_id', $data['subject_id'])
                              ->where('class_subject_teacher.class_id', $this->ownerRecord->id);
                        });
                    }),

                Tables\Filters\SelectFilter::make('specialization')
                    ->options(function () {
                        return Teacher::whereNotNull('specialization')
                            ->distinct()
                            ->pluck('specialization', 'specialization');
                    }),
            ])
            ->headerActions([
                // Custom create action to handle the three-way relationship
                Tables\Actions\CreateAction::make()
                    ->label('Assign Subject Teacher')
                    ->form([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Teacher')
                            ->options(function () {
                                return Teacher::where('is_active', true)
                                    ->whereNotNull('specialization') // Secondary teachers only
                                    ->get()
                                    ->mapWithKeys(function ($teacher) {
                                        return [$teacher->id => "{$teacher->name} ({$teacher->employee_id}) - {$teacher->specialization}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->helperText('Only secondary teachers with specializations are shown'),
                    ])
                    ->action(function (array $data): void {
                        // Insert into the class_subject_teacher pivot table
                        DB::table('class_subject_teacher')->updateOrInsert([
                            'class_id' => $this->ownerRecord->id,
                            'subject_id' => $data['subject_id'],
                            'teacher_id' => $data['teacher_id'],
                        ], [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->default(function ($record) {
                                return DB::table('class_subject_teacher')
                                    ->where('class_id', $this->ownerRecord->id)
                                    ->where('teacher_id', $record->id)
                                    ->value('subject_id');
                            }),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Teacher')
                            ->options(function () {
                                return Teacher::where('is_active', true)
                                    ->whereNotNull('specialization')
                                    ->get()
                                    ->mapWithKeys(function ($teacher) {
                                        return [$teacher->id => "{$teacher->name} ({$teacher->employee_id}) - {$teacher->specialization}"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->default(function ($record) {
                                return $record->id;
                            }),
                    ])
                    ->action(function ($record, array $data): void {
                        // Update the assignment
                        DB::table('class_subject_teacher')
                            ->where('class_id', $this->ownerRecord->id)
                            ->where('teacher_id', $record->id)
                            ->update([
                                'subject_id' => $data['subject_id'],
                                'teacher_id' => $data['teacher_id'],
                                'updated_at' => now(),
                            ]);
                    }),

                // Custom delete action to handle the three-way relationship
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record): void {
                        DB::table('class_subject_teacher')
                            ->where('class_id', $this->ownerRecord->id)
                            ->where('teacher_id', $record->id)
                            ->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $teacherIds = $records->pluck('id')->toArray();

                            DB::table('class_subject_teacher')
                                ->where('class_id', $this->ownerRecord->id)
                                ->whereIn('teacher_id', $teacherIds)
                                ->delete();
                        }),
                ]),
            ]);
    }
}
