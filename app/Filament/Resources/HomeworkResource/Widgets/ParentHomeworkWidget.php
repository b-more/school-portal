<?php

namespace App\Filament\Widgets;

use App\Models\Homework;
use App\Models\ParentGuardian;
use App\Constants\RoleConstants;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ParentHomeworkWidget extends BaseWidget
{
    protected static ?string $heading = '📚 Recent Homework for Your Children';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->role_id === RoleConstants::PARENT;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(function ($record) {
                        return $record->title;
                    })
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-book-open'),

                Tables\Columns\TextColumn::make('grade.name')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-academic-cap'),

                Tables\Columns\TextColumn::make('assignedBy.name')
                    ->label('Teacher')
                    ->icon('heroicon-o-user'),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime('M j, g:i A')
                    ->color(function ($record) {
                        if ($record->due_date->isPast()) {
                            return 'danger';
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->icon(function ($record) {
                        if ($record->due_date->isPast()) {
                            return 'heroicon-o-exclamation-triangle';
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            return 'heroicon-o-clock';
                        }
                        return 'heroicon-o-calendar';
                    })
                    ->tooltip(function ($record) {
                        if ($record->due_date->isPast()) {
                            return 'Overdue by ' . $record->due_date->diffForHumans();
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            return 'Due soon: ' . $record->due_date->diffForHumans();
                        }
                        return 'Due ' . $record->due_date->diffForHumans();
                    }),

                Tables\Columns\TextColumn::make('max_score')
                    ->label('Max Score')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('homework_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->homework_file))
                    ->icon('heroicon-o-document')
                    ->color('primary')
                    ->tooltip('Homework file available'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'completed',
                        'danger' => 'overdue',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'active',
                        'heroicon-o-check-circle' => 'completed',
                        'heroicon-o-x-circle' => 'overdue',
                    ])
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->due_date->isPast() && $state === 'active') {
                            return 'overdue';
                        }
                        return $state;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (Homework $record) => route('homework.download', $record))
                    ->openUrlInNewTab(false)
                    ->visible(fn (Homework $record) => !empty($record->homework_file))
                    ->tooltip('Download homework file'),

                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Homework $record) => route('homework.view', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Homework $record) => !empty($record->homework_file))
                    ->tooltip('View homework file in browser'),

                Tables\Actions\Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->modalHeading(fn (Homework $record) => 'Homework Details: ' . $record->title)
                    ->modalContent(function (Homework $record) {
                        $content = "<div class='space-y-4'>";
                        $content .= "<div><strong>Subject:</strong> {$record->subject->name}</div>";
                        $content .= "<div><strong>Grade:</strong> {$record->grade->name}</div>";
                        $content .= "<div><strong>Teacher:</strong> {$record->assignedBy->name}</div>";
                        $content .= "<div><strong>Due Date:</strong> {$record->due_date->format('M j, Y g:i A')}</div>";
                        if ($record->max_score) {
                            $content .= "<div><strong>Max Score:</strong> {$record->max_score}</div>";
                        }
                        if ($record->description) {
                            $content .= "<div><strong>Instructions:</strong><br>" . nl2br(e($record->description)) . "</div>";
                        }
                        $content .= "<div><strong>Status:</strong> " . ucfirst($record->status) . "</div>";

                        if ($record->due_date->isPast()) {
                            $content .= "<div class='text-red-600'><strong>⚠️ This homework is overdue!</strong></div>";
                        } elseif ($record->due_date->diffInDays() <= 2) {
                            $content .= "<div class='text-yellow-600'><strong>⏰ This homework is due soon!</strong></div>";
                        }

                        $content .= "</div>";
                        return new \Illuminate\Support\HtmlString($content);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->relationship('subject', 'name')
                    ->placeholder('All Subjects'),

                Tables\Filters\SelectFilter::make('grade')
                    ->relationship('grade', 'name')
                    ->placeholder('All Grades'),

                Tables\Filters\Filter::make('due_soon')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<=', now()->addDays(3)))
                    ->label('Due Within 3 Days')
                    ->toggle(),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<', now()))
                    ->label('Overdue')
                    ->toggle(),
            ])
            ->defaultSort('due_date', 'asc')
            ->paginated([5, 10, 15])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No Homework Found')
            ->emptyStateDescription('There is no homework available for your children at the moment.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

        if (!$parentGuardian) {
            return Homework::query()->whereRaw('1 = 0'); // Return empty result
        }

        // Get children's grade IDs
        $childrenGradeIds = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('grade_id')
            ->unique();

        if ($childrenGradeIds->isEmpty()) {
            return Homework::query()->whereRaw('1 = 0'); // Return empty result
        }

        return Homework::query()
            ->whereIn('grade_id', $childrenGradeIds)
            ->where('status', 'active')
            ->where('due_date', '>=', now()->subDays(60)) // Show homework from last 60 days
            ->with(['subject', 'grade', 'assignedBy']);
    }

    protected function getTableHeading(): string
    {
        $user = Auth::user();
        $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

        if (!$parentGuardian) {
            return '📚 Recent Homework';
        }

        $childrenCount = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->count();

        $children = $parentGuardian->students()
            ->where('enrollment_status', 'active')
            ->pluck('name')
            ->take(2)
            ->join(', ');

        if ($childrenCount > 2) {
            $children .= ' and ' . ($childrenCount - 2) . ' more';
        }

        return "📚 Recent Homework for {$children}";
    }
}
