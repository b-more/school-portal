<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Book;
use App\Models\BookLoan;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class LibrarianDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.librarian-dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getLibraryStats()
    {
        return [
            'total_books' => Book::sum('total_copies'),
            'unique_titles' => Book::where('is_active', true)->count(),
            'available_copies' => Book::sum('available_copies'),
            'books_on_loan' => Book::sum(DB::raw('total_copies - available_copies')),
        ];
    }

    public function getLoanStats()
    {
        $activeLoans = BookLoan::where('status', 'active')->count();
        $overdueLoans = BookLoan::where('status', 'active')
            ->where('due_date', '<', now())
            ->count();
        $totalLoansThisMonth = BookLoan::whereMonth('lent_at', now()->month)
            ->whereYear('lent_at', now()->year)
            ->count();
        $returnsThisMonth = BookLoan::whereMonth('returned_at', now()->month)
            ->whereYear('returned_at', now()->year)
            ->count();

        return [
            'active_loans' => $activeLoans,
            'overdue_loans' => $overdueLoans,
            'loans_this_month' => $totalLoansThisMonth,
            'returns_this_month' => $returnsThisMonth,
        ];
    }

    public function getOverdueLoans()
    {
        return BookLoan::where('status', 'active')
            ->where('due_date', '<', now())
            ->with(['student.grade', 'student.classSection', 'book'])
            ->orderBy('due_date')
            ->take(10)
            ->get();
    }

    public function getRecentLoans()
    {
        return BookLoan::with(['student', 'book', 'lentBy'])
            ->latest('lent_at')
            ->take(10)
            ->get();
    }

    public function getRecentReturns()
    {
        return BookLoan::whereNotNull('returned_at')
            ->with(['student', 'book', 'returnedTo'])
            ->latest('returned_at')
            ->take(10)
            ->get();
    }

    public function getStudentsWithFines()
    {
        return BookLoan::where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->with(['student.grade', 'student.classSection'])
            ->select('student_id', DB::raw('SUM(fine_amount) as total_fines'))
            ->groupBy('student_id')
            ->orderByDesc('total_fines')
            ->take(10)
            ->get();
    }

    public function getLowStockBooks()
    {
        return Book::where('is_active', true)
            ->whereColumn('available_copies', '<=', DB::raw('total_copies * 0.2'))
            ->orWhere(function ($query) {
                $query->where('available_copies', '<=', 2)
                    ->where('available_copies', '>', 0);
            })
            ->orderBy('available_copies')
            ->take(10)
            ->get();
    }

    public function getPopularBooks()
    {
        return Book::withCount(['loans' => function ($query) {
            $query->whereMonth('lent_at', now()->month);
        }])
            ->having('loans_count', '>', 0)
            ->orderByDesc('loans_count')
            ->take(10)
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_book')
                ->label('Add Book')
                ->icon('heroicon-o-plus-circle')
                ->url(route('filament.admin.resources.books.create'))
                ->color('success'),

            Action::make('lend_book')
                ->label('Lend Book')
                ->icon('heroicon-o-arrow-right-circle')
                ->url(route('filament.admin.resources.book-loans.create'))
                ->color('primary'),

            Action::make('view_books')
                ->label('All Books')
                ->icon('heroicon-o-book-open')
                ->url(route('filament.admin.resources.books.index')),

            Action::make('student_clearance')
                ->label('Student Clearance')
                ->icon('heroicon-o-clipboard-document-check')
                ->url(route('filament.admin.pages.student-clearance'))
                ->color('warning'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role_id === RoleConstants::LIBRARIAN ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::LIBRARIAN ?? false;
    }
}
