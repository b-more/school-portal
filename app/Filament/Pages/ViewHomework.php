<?php

namespace App\Filament\Pages;

use App\Models\Homework;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ViewHomework extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'View Homework';
    protected static ?string $navigationGroup = 'Student Information';
    protected static string $view = 'filament.pages.view-homework';
    protected static ?int $navigationSort = 2;

    protected ?string $heading = 'Homework';

    public static function canAccess(): bool
    {
        // Only accessible to parents and students
        return auth()->user()->hasRole(['parent', 'student']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Subject')
                    ->searchable(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Grade')
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('Teacher')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted On')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (Homework $record) => route('homework.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Homework $record) => !empty($record->homework_file)),

                Tables\Actions\Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Homework $record) => route('filament.admin.pages.homework-details', [
                        'homeworkId' => $record->id,
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('parent')) {
            // For parents, show homework for all their children
            $studentIds = $user->parentGuardian->students()
                ->where('enrollment_status', 'active')
                ->pluck('id');

            $grades = Student::whereIn('id', $studentIds)
                ->pluck('grade')
                ->unique();

            return Homework::whereIn('grade', $grades)
                ->where('status', 'active');
        } else {
            // For students, show homework for their grade only
            $student = $user->student;

            if (!$student) {
                return Homework::whereRaw('0 = 1'); // Return empty query
            }

            return Homework::where('grade', $student->grade)
                ->where('status', 'active');
        }
    }
}
