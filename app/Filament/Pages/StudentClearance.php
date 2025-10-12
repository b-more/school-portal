<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Student;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentClearance extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static string $view = 'filament.pages.student-clearance';

    protected static ?string $navigationLabel = 'Student Clearance';

    protected static ?string $navigationGroup = 'Library Management';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::LIBRARIAN]) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, [RoleConstants::ADMIN, RoleConstants::LIBRARIAN]) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->with(['grade', 'classSection'])
                    ->withCount([
                        'bookLoans as active_loans_count' => fn (Builder $query) => $query->whereIn('status', ['active', 'overdue']),
                        'bookLoans as overdue_loans_count' => fn (Builder $query) => $query->where('status', 'overdue')
                            ->orWhere(function ($q) {
                                $q->where('status', 'active')->where('due_date', '<', now());
                            }),
                        'bookLoans as unpaid_fines_count' => fn (Builder $query) => $query->where('fine_paid', false)
                            ->where('fine_amount', '>', 0),
                    ])
                    ->withSum([
                        'bookLoans as total_fines' => fn (Builder $query) => $query->where('fine_paid', false),
                    ], 'fine_amount')
            )
            ->columns([
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Student $record): string => "{$record->grade->name} {$record->classSection->name}"),

                TextColumn::make('active_loans_count')
                    ->label('Active Loans')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('overdue_loans_count')
                    ->label('Overdue')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('total_fines')
                    ->label('Outstanding Fines')
                    ->money('ZMW')
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success'),

                IconColumn::make('is_cleared')
                    ->label('Cleared')
                    ->getStateUsing(function (Student $record) {
                        return $record->active_loans_count === 0 && $record->unpaid_fines_count === 0;
                    })
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view_loans')
                    ->label('View Loans')
                    ->icon('heroicon-o-book-open')
                    ->modalHeading(fn (Student $record) => "Book Loans - {$record->name}")
                    ->modalContent(function (Student $record) {
                        $loans = $record->bookLoans()->with('book')->latest()->get();

                        if ($loans->isEmpty()) {
                            return view('filament.components.empty-state', ['message' => 'No book loans found']);
                        }

                        $html = '<div class="space-y-3">';
                        foreach ($loans as $loan) {
                            $statusColor = match ($loan->status) {
                                'returned' => 'success',
                                'overdue' => 'danger',
                                'active' => 'primary',
                                default => 'gray',
                            };

                            $html .= '<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">';
                            $html .= '<div class="flex justify-between items-start">';
                            $html .= '<div>';
                            $html .= '<p class="font-semibold">'.$loan->book->title.'</p>';
                            $html .= '<p class="text-sm text-gray-600 dark:text-gray-400">by '.$loan->book->author.'</p>';
                            $html .= '</div>';
                            $html .= '<span class="px-2 py-1 text-xs rounded-full bg-'.$statusColor.'-100 text-'.$statusColor.'-800 dark:bg-'.$statusColor.'-900 dark:text-'.$statusColor.'-200">'.ucfirst($loan->status).'</span>';
                            $html .= '</div>';
                            $html .= '<div class="mt-2 text-sm space-y-1">';
                            $html .= '<p><strong>Lent:</strong> '.$loan->lent_date->format('M d, Y').'</p>';
                            $html .= '<p><strong>Due:</strong> '.$loan->due_date->format('M d, Y').'</p>';
                            if ($loan->returned_at) {
                                $html .= '<p><strong>Returned:</strong> '.$loan->returned_at->format('M d, Y').'</p>';
                            }
                            if ($loan->fine_amount > 0) {
                                $html .= '<p><strong>Fine:</strong> ZMW '.number_format($loan->fine_amount, 2).($loan->fine_paid ? ' (Paid)' : ' (Unpaid)').'</p>';
                            }
                            $html .= '</div>';
                            $html .= '</div>';
                        }
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Action::make('clear_student')
                    ->label('Mark as Cleared')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (Student $record) {
                        $activeLoans = $record->bookLoans()->whereIn('status', ['active', 'overdue'])->count();

                        return $activeLoans === 0;
                    })
                    ->requiresConfirmation()
                    ->action(function (Student $record) {
                        // Mark all fines as paid
                        $record->bookLoans()->where('fine_paid', false)->update(['fine_paid' => true]);

                        Notification::make()
                            ->title('Student Cleared')
                            ->body("{$record->name} has been cleared. All fines marked as paid.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('name');
    }
}
