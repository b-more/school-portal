<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LibrarianDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.pages.librarian-dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    public function getLibraryStats()
    {
        return Cache::remember('librarian_dashboard_library_stats', 300, function () {
            return [
                'total_books' => Book::sum('total_copies') ?? 0,
                'unique_titles' => Book::where('is_active', true)->count(),
                'available_copies' => Book::sum('available_copies') ?? 0,
                'books_on_loan' => DB::table('books')->sum(DB::raw('total_copies - available_copies')) ?? 0,
            ];
        });
    }

    public function getLoanStats()
    {
        return Cache::remember('librarian_dashboard_loan_stats', 300, function () {
            return [
                'active_loans' => BookLoan::where('status', 'active')->count(),
                'overdue_loans' => BookLoan::where('status', 'active')
                    ->where('due_date', '<', now())
                    ->count(),
                'loans_this_month' => BookLoan::whereMonth('lent_at', now()->month)
                    ->whereYear('lent_at', now()->year)
                    ->count(),
                'returns_this_month' => BookLoan::whereMonth('returned_at', now()->month)
                    ->whereYear('returned_at', now()->year)
                    ->count(),
            ];
        });
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
        // Get student IDs with their total fines
        $studentFines = BookLoan::where('fine_amount', '>', 0)
            ->where('fine_paid', false)
            ->select('student_id', DB::raw('SUM(fine_amount) as total_fines'))
            ->groupBy('student_id')
            ->orderByDesc('total_fines')
            ->take(10)
            ->pluck('total_fines', 'student_id');

        if ($studentFines->isEmpty()) {
            return collect();
        }

        // Eager load students with their relationships
        $students = Student::with(['grade', 'classSection'])
            ->whereIn('id', $studentFines->keys())
            ->get()
            ->map(function ($student) use ($studentFines) {
                $student->total_fines = $studentFines[$student->id];

                return $student;
            })
            ->sortByDesc('total_fines');

        return $students;
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
