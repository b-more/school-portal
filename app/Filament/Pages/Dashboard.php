<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\Event;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Result;
use App\Models\SmsLog;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\FeeStructure;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?int $navigationSort = 1;

    public function mount()
    {
        // Log all fees and payments for debugging
        $this->logSystemData();
    }

    protected function logSystemData()
    {
        // Log all fee structures
        $allFeeStructures = FeeStructure::all();
        Log::info('ALL FEE STRUCTURES', [
            'count' => $allFeeStructures->count(),
            'list' => $allFeeStructures->map(function($fs) {
                return [
                    'id' => $fs->id,
                    'grade' => $fs->grade,
                    'term' => $fs->term,
                    'year' => $fs->academic_year,
                    'is_active' => $fs->is_active,
                    'total_fee' => $fs->total_fee
                ];
            })
        ]);

        // Log all payments
        $allPayments = StudentFee::where('amount_paid', '>', 0)->get();
        Log::info('ALL PAYMENTS', [
            'count' => $allPayments->count(),
            'list' => $allPayments->map(function($p) {
                return [
                    'id' => $p->id,
                    'student_id' => $p->student_id,
                    'fee_structure_id' => $p->fee_structure_id,
                    'amount_paid' => $p->amount_paid,
                    'status' => $p->payment_status
                ];
            })
        ]);
    }

    public function getStats(): array
    {
        // Get student counts
        $totalStudents = Student::count();
        $activeStudents = Student::where('enrollment_status', 'active')->count();
        $inactiveStudents = Student::where('enrollment_status', 'inactive')->count();

        // Get staff counts
        $totalStaff = Employee::count();
        $teacherCount = Employee::where('role', 'teacher')->count();
        $adminCount = Employee::where('role', 'admin')->count();

        // Get fee collection statistics - COMPLETELY REVISED
        // First, get total amount paid from all student fees
        $totalFeesCollected = StudentFee::sum('amount_paid');
        Log::info('Total fees collected', ['amount' => $totalFeesCollected]);

        // Calculate total expected from all fee structures assigned to students
        $totalFeesExpected = StudentFee::join('fee_structures', 'student_fees.fee_structure_id', '=', 'fee_structures.id')
            ->sum('fee_structures.total_fee');

        // If no expected fees yet (no fee structures assigned)
        if ($totalFeesExpected == 0) {
            // Use sum of paid + balance as expected
            $totalFeesExpected = StudentFee::sum(DB::raw('amount_paid + balance'));
        }

        // If still no expected fees but some collections exist
        if ($totalFeesExpected == 0 && $totalFeesCollected > 0) {
            // Estimate expected fees as 3x the collected amount (assuming ~33% collection rate)
            $totalFeesExpected = $totalFeesCollected * 3;
        }

        // If no data at all, use placeholder
        if ($totalFeesExpected == 0) {
            $totalFeesExpected = 1; // Avoid division by zero
        }

        Log::info('Fee calculation', [
            'collected' => $totalFeesCollected,
            'expected' => $totalFeesExpected
        ]);

        $collectionRate = round(($totalFeesCollected / $totalFeesExpected) * 100);

        // Get homework statistics
        $totalHomework = Homework::count();
        $activeHomework = Homework::where('status', 'active')->count();
        $pendingSubmissions = HomeworkSubmission::where('status', 'submitted')->count();

        // Get SMS statistics
        $monthlySMSCount = SmsLog::whereMonth('created_at', date('m'))
                            ->whereYear('created_at', date('Y'))
                            ->count();
        $monthlySMSCost = SmsLog::whereMonth('created_at', date('m'))
                            ->whereYear('created_at', date('Y'))
                            ->sum('cost');

        return [
            Stat::make('Total Students', $totalStudents)
                ->description("Active: $activeStudents | Inactive: $inactiveStudents")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([7, 2, 10, 3, 15, 4, $activeStudents])
                ->color('primary'),

            Stat::make('Total Staff', $totalStaff)
                ->description("Teachers: $teacherCount | Admin: $adminCount")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([2, 3, 5, 4, 5, 6, $teacherCount])
                ->color('success'),

            Stat::make('Fee Collection', "ZMW " . number_format($totalFeesCollected, 2))
                ->description("$collectionRate% Collected")
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([15, 30, 20, 45, 35, 40, $collectionRate])
                ->color($collectionRate >= 70 ? 'success' : ($collectionRate >= 40 ? 'warning' : 'danger')),

            Stat::make('Homework', $totalHomework)
                ->description("Active: $activeHomework | Pending: $pendingSubmissions")
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([5, 10, 15, 20, 15, 10, $activeHomework])
                ->color('info'),

            Stat::make('SMS Notifications', $monthlySMSCount)
                ->description("Cost: ZMW " . number_format($monthlySMSCost, 2))
                ->descriptionIcon('heroicon-m-chat-bubble-left')
                ->chart([10, 20, 30, 40, 20, 10, $monthlySMSCount])
                ->color('warning'),
        ];
    }

    // Rest of the Dashboard class remains unchanged
    public function getQuickActions(): array
    {
        $actions = [];

        // Only add routes that exist
        if ($this->routeExists('filament.admin.resources.students.create')) {
            $actions[] = [
                'title' => 'Add Student',
                'icon' => 'heroicon-o-user-plus',
                'color' => 'primary',
                'url' => route('filament.admin.resources.students.create'),
            ];
        }

        if ($this->routeExists('filament.admin.resources.student-fees.create')) {
            $actions[] = [
                'title' => 'Record Payment',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'success',
                'url' => route('filament.admin.resources.student-fees.create'),
            ];
        }

        // Check for different homework route variations
        if ($this->routeExists('filament.admin.resources.homework.create')) {
            $actions[] = [
                'title' => 'New Homework',
                'icon' => 'heroicon-o-document-plus',
                'color' => 'warning',
                'url' => route('filament.admin.resources.homework.create'),
            ];
        } elseif ($this->routeExists('filament.admin.resources.homeworks.create')) {
            $actions[] = [
                'title' => 'New Homework',
                'icon' => 'heroicon-o-document-plus',
                'color' => 'warning',
                'url' => route('filament.admin.resources.homeworks.create'),
            ];
        }

        // Check for different SMS route variations
        if ($this->routeExists('filament.admin.resources.sms-logs.create')) {
            $actions[] = [
                'title' => 'Send SMS',
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'danger',
                'url' => route('filament.admin.resources.sms-logs.create'),
            ];
        } elseif ($this->routeExists('filament.admin.resources.teacher-assignments.index')) {
            $actions[] = [
                'title' => 'Send SMS',
                'icon' => 'heroicon-o-paper-airplane',
                'color' => 'danger',
                'url' => route('filament.admin.resources.teacher-assignments.index'),
            ];
        }

        if ($this->routeExists('filament.admin.resources.employees.create')) {
            $actions[] = [
                'title' => 'Add Teacher',
                'icon' => 'heroicon-o-academic-cap',
                'color' => 'info',
                'url' => route('filament.admin.resources.employees.create'),
            ];
        }

        return $actions;
    }

    protected function routeExists($name)
    {
        return Route::has($name);
    }

    public function getRecentActivity(): array
    {
        // Get recent students
        $recentStudents = Student::latest()->take(5)->get();

        // Get recent fee payments
        $recentPayments = StudentFee::where('payment_status', '!=', 'unpaid')
                        ->latest()
                        ->take(5)
                        ->with(['student', 'feeStructure'])
                        ->get();

        // Get recent homework submissions
        $recentSubmissions = HomeworkSubmission::latest()
                            ->take(5)
                            ->with(['student', 'homework'])
                            ->get();

        // Get recent SMS logs
        $recentSms = SmsLog::latest()
                    ->take(5)
                    ->get();

        return [
            'students' => $recentStudents,
            'payments' => $recentPayments,
            'submissions' => $recentSubmissions,
            'sms' => $recentSms,
        ];
    }

    public function getUpcomingEvents(): array
    {
        // Convert the collection to an array
        return Event::where('start_date', '>=', now())
                ->orderBy('start_date')
                ->take(5)
                ->get()
                ->toArray();
    }

    public function getChartData(): array
    {
        // Get enrollment by grade
        $gradeData = Student::where('enrollment_status', 'active')
                    ->select('grade', DB::raw('count(*) as count'))
                    ->groupBy('grade')
                    ->orderBy('grade')
                    ->get()
                    ->toArray();

        // Get fee collection by grade
        $feeData = StudentFee::join('students', 'student_fees.student_id', '=', 'students.id')
                    ->select('students.grade',
                             DB::raw('sum(student_fees.amount_paid) as collected'),
                             DB::raw('sum(student_fees.balance) as balance'))
                    ->groupBy('students.grade')
                    ->orderBy('students.grade')
                    ->get()
                    ->toArray();

        // Get subject performance
        $resultData = Result::join('subjects', 'results.subject_id', '=', 'subjects.id')
                    ->select('subjects.name', DB::raw('avg(results.marks) as average'))
                    ->groupBy('subjects.name')
                    ->orderBy('average', 'desc')
                    ->take(5)
                    ->get()
                    ->toArray();

        return [
            'gradeData' => $gradeData,
            'feeData' => $feeData,
            'resultData' => $resultData,
        ];
    }

    protected function getCurrentTerm(): string
    {
        $month = date('n');

        if ($month >= 1 && $month <= 4) {
            return 'Term 1';
        } elseif ($month >= 5 && $month <= 8) {
            return 'Term 2';
        } else {
            return 'Term 3';
        }
    }

    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'quickActions' => $this->getQuickActions(),
            'recentActivity' => $this->getRecentActivity(),
            'upcomingEvents' => $this->getUpcomingEvents(),
            'chartData' => $this->getChartData(),
        ];
    }
}
