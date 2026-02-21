<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Services\Accounting\FinancialReportService;
use Filament\Pages\Page;
use Carbon\Carbon;

class AccountsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Accounts Dashboard';
    protected static ?int $navigationSort = 0;
    protected static ?string $slug = 'accounts-dashboard';
    protected static ?string $title = 'Accounts Dashboard';
    protected static string $view = 'filament.pages.accounts-dashboard';

    public $period = 'month';
    public $startDate;
    public $endDate;

    public $totalIncome = 0;
    public $totalExpenses = 0;
    public $netIncome = 0;
    public $bankBalances = [];
    public $incomeByCategory = [];
    public $expensesByCategory = [];
    public $monthlyTrend = [];
    public $outstandingPayables = 0;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role_id === RoleConstants::ADMIN;
    }

    public function mount(): void
    {
        $this->setPeriod('month');
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        switch ($period) {
            case 'week':
                $this->startDate = Carbon::now()->startOfWeek();
                $this->endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $this->startDate = Carbon::now()->startOfMonth();
                $this->endDate = Carbon::now()->endOfMonth();
                break;
            case 'quarter':
                $this->startDate = Carbon::now()->startOfQuarter();
                $this->endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $this->startDate = Carbon::now()->startOfYear();
                $this->endDate = Carbon::now()->endOfYear();
                break;
            default:
                $this->startDate = Carbon::now()->startOfMonth();
                $this->endDate = Carbon::now()->endOfMonth();
        }

        $this->loadData();
    }

    public function loadData(): void
    {
        $reportService = app(FinancialReportService::class);

        // Get income vs expense comparison
        $comparison = $reportService->getIncomeExpenseComparison($this->startDate, $this->endDate);
        $this->totalIncome = $comparison['total_income'];
        $this->totalExpenses = $comparison['total_expenses'];
        $this->netIncome = $comparison['net_income'];

        // Get bank balances
        $this->bankBalances = $reportService->getBankBalances();

        // Get income by category
        $incomeSummary = $reportService->getIncomeSummary($this->startDate, $this->endDate);
        $this->incomeByCategory = $incomeSummary['by_account'];

        // Get expenses by category
        $expenseSummary = $reportService->getExpenseSummary($this->startDate, $this->endDate);
        $this->expensesByCategory = $expenseSummary['by_category'];

        // Get monthly trend for current year
        $this->monthlyTrend = $reportService->getMonthlyTrend(Carbon::now()->year);

        // Get outstanding payables
        $payables = $reportService->getOutstandingPayables();
        $this->outstandingPayables = $payables['total_outstanding'];
    }

    public function getPeriodLabel(): string
    {
        return match ($this->period) {
            'week' => 'This Week',
            'month' => 'This Month',
            'quarter' => 'This Quarter',
            'year' => 'This Year',
            default => 'This Month',
        };
    }
}
