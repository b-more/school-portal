<?php

namespace App\Filament\Resources\SchoolClassResource\RelationManagers;

use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';
    protected static ?string $title = 'Class Teachers';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(function () {
                        return Teacher::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($teacher) {
                                $type = $teacher->isPrimaryTeacher() ? 'Primary' : 'Secondary';
                                return [$teacher->id => "{$teacher->name} ({$teacher->employee_id}) - {$type}"];
                            });
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('role')
                    ->label('Role')
                    ->options([
                        'class_teacher' => 'Class Teacher',
                        'assistant_teacher' => 'Assistant Teacher',
                        'subject_teacher' => 'Subject Teacher',
                    ])
                    ->required()
                    ->default('subject_teacher'),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Is Primary Teacher')
                    ->default(false)
                    ->helperText('Mark as primary teacher for this class'),
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

                Tables\Columns\TextColumn::make('qualification')
                    ->label('Qualification')
                    ->searchable(),

                Tables\Columns\TextColumn::make('specialization')
                    ->label('Specialization')
                    ->searchable()
                    ->placeholder('Primary Teacher'),

                Tables\Columns\TextColumn::make('pivot.role')
                    ->label('Role in Class')
                    ->badge()
                    ->color(function ($state) {
                        return match($state) {
                            'class_teacher' => 'success',
                            'assistant_teacher' => 'info',
                            'subject_teacher' => 'warning',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'class_teacher' => 'Class Teacher',
                            'assistant_teacher' => 'Assistant Teacher',
                            'subject_teacher' => 'Subject Teacher',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    }),

                Tables\Columns\IconColumn::make('pivot.is_primary')
                    ->label('Primary Teacher')
                    ->boolean(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role in Class')
                    ->options([
                        'class_teacher' => 'Class Teacher',
                        'assistant_teacher' => 'Assistant Teacher',
                        'subject_teacher' => 'Subject Teacher',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        return $query->wherePivot('role', $data['value']);
                    }),

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Teachers Only')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === true) {
                            return $query->wherePivot('is_primary', true);
                        } elseif ($data['value'] === false) {
                            return $query->wherePivot('is_primary', false);
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('teacher_type')
                    ->label('Teacher Type')
                    ->options([
                        'primary' => 'Primary Teacher',
                        'secondary' => 'Secondary Teacher',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'] === 'primary', function ($query) {
                            return $query->whereNull('specialization');
                        })->when($data['value'] === 'secondary', function ($query) {
                            return $query->whereNotNull('specialization');
                        });
                    }),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Assign Teacher')
                    ->color('primary')
                    ->icon('heroicon-o-plus')
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->where('is_active', true))
                    ->recordSelectSearchColumns(['name', 'employee_id', 'specialization'])
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Select Teacher')
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                $type = $record->isPrimaryTeacher() ? 'Primary' : 'Secondary';
                                return "{$record->name} ({$record->employee_id}) - {$type}";
                            }),
                        Forms\Components\Select::make('role')
                            ->label('Role in Class')
                            ->options([
                                'class_teacher' => 'Class Teacher',
                                'assistant_teacher' => 'Assistant Teacher',
                                'subject_teacher' => 'Subject Teacher',
                            ])
                            ->required()
                            ->default('subject_teacher'),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Is Primary Teacher')
                            ->default(false)
                            ->helperText('Only one teacher per class should be marked as primary'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options([
                                'class_teacher' => 'Class Teacher',
                                'assistant_teacher' => 'Assistant Teacher',
                                'subject_teacher' => 'Subject Teacher',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Is Primary Teacher')
                            ->default(false),
                    ])
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['role'] = $data['pivot']['role'] ?? 'subject_teacher';
                        $data['is_primary'] = $data['pivot']['is_primary'] ?? false;
                        return $data;
                    })
                    ->mutateFormDataUsing(function (array $data): array {
                        return [
                            'role' => $data['role'],
                            'is_primary' => $data['is_primary'],
                        ];
                    }),

                Tables\Actions\DetachAction::make(),

                Tables\Actions\Action::make('view_assignments')
                    ->label('View Assignments')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn ($record) => "Teaching Assignments - {$record->name}")
                    ->modalContent(function ($record) {
                        $summary = $record->getTeachingSummary();

                        $content = "<div class='space-y-4'>";
                        $content .= "<div><strong>Teacher Type:</strong> {$summary['teacher_type']}</div>";
                        $content .= "<div><strong>Total Students:</strong> {$summary['total_students']}</div>";
                        $content .= "<div><strong>Total Subjects:</strong> {$summary['total_subjects']}</div>";
                        $content .= "<div><strong>Class Sections:</strong> {$summary['total_class_sections']}</div>";

                        if (!$summary['assignments']->isEmpty()) {
                            $content .= "<div><strong>Current Assignments:</strong><ul class='mt-2 space-y-1'>";
                            foreach ($summary['assignments'] as $assignment) {
                                $classSection = $assignment['class_section'];
                                $subjects = $assignment['subjects']->pluck('name')->join(', ');
                                $studentCount = $assignment['student_count'];
                                $content .= "<li>• {$classSection->grade->name} - {$classSection->name} ({$studentCount} students)<br>&nbsp;&nbsp;Subjects: {$subjects}</li>";
                            }
                            $content .= "</ul></div>";
                        }

                        $content .= "</div>";
                        return new \Illuminate\Support\HtmlString($content);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),

                    Tables\Actions\BulkAction::make('set_as_class_teachers')
                        ->label('Set as Class Teachers')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['role' => 'class_teacher']);
                            }
                        })
                        ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('set_as_subject_teachers')
                        ->label('Set as Subject Teachers')
                        ->icon('heroicon-o-book-open')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->pivot->update(['role' => 'subject_teacher']);
                            }
                        })
                        ->requiresConfirmation(),
                ]),
            ]);
    }
}
