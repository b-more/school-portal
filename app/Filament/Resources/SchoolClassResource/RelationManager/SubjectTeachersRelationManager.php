<?php

namespace App\Filament\Resources\SchoolClassResource\RelationManagers;

use App\Models\Subject;
use App\Models\Employee;
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

    // Fix: Changed from string to ?string to match the parent class
    protected static ?string $title = 'Subject-Teacher Assignments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('employee_id')
                    ->label('Teacher')
                    ->options(Employee::query()
                        ->where('department', 'Secondary')
                        ->where('status', 'active')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
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

                // Using a custom accessor to get the subject name
                Tables\Columns\TextColumn::make('subject_name')
                    ->label('Subject')
                    ->getStateUsing(function ($record): string {
                        $subjectId = DB::table('class_subject_teacher')
                            ->where('class_id', $this->ownerRecord->id)
                            ->where('employee_id', $record->id)
                            ->value('subject_id');

                        return Subject::find($subjectId)?->name ?? 'Unknown';
                    }),

                Tables\Columns\TextColumn::make('position')
                    ->label('Position'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),
            ])
            ->filters([
                // Filter by subject
                Tables\Filters\Filter::make('subject')
                    ->form([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::query()->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['subject_id'])) {
                            return $query;
                        }

                        return $query->whereHas('subjectTeachers', function ($q) use ($data) {
                            $q->where('class_subject_teacher.subject_id', $data['subject_id'])
                              ->where('class_subject_teacher.class_id', $this->ownerRecord->id);
                        });
                    }),
            ])
            ->headerActions([
                // Custom create action to handle the three-way relationship
                Tables\Actions\CreateAction::make()
                    ->label('Assign Subject Teacher')
                    ->form([
                        Forms\Components\Select::make('subject_id')
                            ->label('Subject')
                            ->options(Subject::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('employee_id')
                            ->label('Teacher')
                            ->options(Employee::query()
                                ->where('department', 'Secondary')
                                ->where('status', 'active')
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        // Insert into the class_subject_teacher pivot table
                        DB::table('class_subject_teacher')->insert([
                            'class_id' => $this->ownerRecord->id,
                            'subject_id' => $data['subject_id'],
                            'employee_id' => $data['employee_id'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }),
            ])
            ->actions([
                // Custom delete action to handle the three-way relationship
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record): void {
                        DB::table('class_subject_teacher')
                            ->where('class_id', $this->ownerRecord->id)
                            ->where('employee_id', $record->id)
                            ->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $employeeIds = $records->pluck('id')->toArray();

                            DB::table('class_subject_teacher')
                                ->where('class_id', $this->ownerRecord->id)
                                ->whereIn('employee_id', $employeeIds)
                                ->delete();
                        }),
                ]),
            ]);
    }
}
