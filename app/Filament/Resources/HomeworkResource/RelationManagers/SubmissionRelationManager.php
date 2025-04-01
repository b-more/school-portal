<?php

namespace App\Filament\Resources\HomeworkResource\RelationManagers;

use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Submission Details')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Student Comments')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('file_attachment')
                            ->label('Submission Files')
                            ->directory('homework-submissions')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->required()
                            ->default(now()),
                        Forms\Components\Toggle::make('is_late')
                            ->label('Mark as Late Submission')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Grading')
                    ->schema([
                        Forms\Components\TextInput::make('marks')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(fn ($livewire) => $livewire->getOwnerRecord()->max_score ?? 100)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('status', 'graded');
                                    $set('graded_at', Carbon::now());
                                    $set('graded_by', auth()->id());
                                }
                            }),
                        Forms\Components\Select::make('status')
                            ->options([
                                'submitted' => 'Submitted',
                                'graded' => 'Graded',
                                'returned' => 'Returned',
                            ])
                            ->default('submitted')
                            ->required(),
                        Forms\Components\Textarea::make('feedback')
                            ->label('Feedback for Student')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('teacher_notes')
                            ->label('Private Teacher Notes')
                            ->helperText('These notes are only visible to teachers, not to students')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('graded_by')
                            ->relationship('gradedBy', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),
                        Forms\Components\DateTimePicker::make('graded_at')
                            ->default(now()),
                    ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.grade')
                    ->label('Grade')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn ($record) => $record->is_late ? "{$record->status} (Late)" : $record->status)
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'graded',
                        'info' => 'returned',
                    ]),
                Tables\Columns\TextColumn::make('marks')
                    ->formatStateUsing(function ($record) {
                        if ($record->marks === null) {
                            return '-';
                        }

                        $maxScore = $record->homework?->max_score ?? 100;
                        return "{$record->marks}/{$maxScore} (" . round(($record->marks / $maxScore) * 100) . "%)";
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('file_attachment')
                    ->label('Files')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->file_attachment)),
                Tables\Columns\TextColumn::make('gradedBy.name')
                    ->label('Graded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student')
                    ->relationship('student', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'graded' => 'Graded',
                        'returned' => 'Returned',
                    ]),
                Tables\Filters\Filter::make('is_late')
                    ->query(fn (Builder $query): Builder => $query->where('is_late', true))
                    ->label('Late Submissions')
                    ->toggle(),
                Tables\Filters\Filter::make('has_marks')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('marks'))
                    ->label('Graded Submissions')
                    ->toggle(),
                Tables\Filters\Filter::make('no_marks')
                    ->query(fn (Builder $query): Builder => $query->whereNull('marks'))
                    ->label('Ungraded Submissions')
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        // Set the homework_id to the owner record ID
                        return $data;
                    }),
                Tables\Actions\Action::make('createMissing')
                    ->label('Create Missing Submissions')
                    ->icon('heroicon-o-plus-circle')
                    ->action(function ($livewire) {
                        $homework = $livewire->getOwnerRecord();

                        // Get all students in this grade
                        $students = Student::where('grade', $homework->grade)
                            ->where('enrollment_status', 'active')
                            ->get();

                        // Get existing submissions
                        $existingSubmissions = $homework->submissions()
                            ->pluck('student_id')
                            ->toArray();

                        // Filter students who haven't submitted
                        $missingStudents = $students->filter(function ($student) use ($existingSubmissions) {
                            return !in_array($student->id, $existingSubmissions);
                        });

                        if ($missingStudents->isEmpty()) {
                            Notification::make()
                                ->title('No missing submissions')
                                ->body('All students have already submitted this homework')
                                ->info()
                                ->send();
                            return;
                        }

                        // Create empty submissions for missing students
                        foreach ($missingStudents as $student) {
                            $homework->submissions()->create([
                                'student_id' => $student->id,
                                'submitted_at' => now(),
                                'status' => 'submitted',
                                'is_late' => $homework->isLateSubmission(),
                            ]);
                        }

                        Notification::make()
                            ->title('Missing submissions created')
                            ->body("Created {$missingStudents->count()} missing submissions for students in {$homework->grade}")
                            ->success()
                            ->send();

                        // Refresh the relation manager
                        $livewire->refresh();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('grade')
                    ->label('Grade')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Forms\Components\TextInput::make('marks')
                            ->label('Score')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(fn ($livewire) => $livewire->getOwnerRecord()->max_score ?? 100),
                        Forms\Components\Textarea::make('feedback')
                            ->required(),
                        Forms\Components\Textarea::make('teacher_notes')
                            ->label('Private Notes (Teacher Only)')
                            ->helperText('These notes won\'t be visible to students'),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'marks' => $data['marks'],
                            'feedback' => $data['feedback'],
                            'teacher_notes' => $data['teacher_notes'] ?? null,
                            'status' => 'graded',
                            'graded_by' => auth()->id(),
                            'graded_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Submission graded successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'submitted'),
                Tables\Actions\Action::make('download')
                    ->label('Download Files')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.resources.homework-submissions.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => !empty($record->file_attachment)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_grade')
                        ->label('Bulk Mark as Graded')
                        ->icon('heroicon-o-check')
                        ->action(function (Builder $query) {
                            // Update all selected records to graded status
                            $query->update([
                                'status' => 'graded',
                                'graded_by' => auth()->id(),
                                'graded_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Submissions marked as graded')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
