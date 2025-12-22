<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Services\Accounting\FinancialReportService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Carbon\Carbon;

class FinancialReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Accounts & Finance';
    protected static ?string $navigationLabel = 'Financial Reports';
    protected static ?int $navigationSort = 9;
    protected static ?string $slug = 'financial-reports';
    protected static ?string $title = 'Financial Reports';
    protected static string $view = 'filament.pages.financial-reports';

    public ?string $reportType = 'income_expense';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public array $reportData = [];

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
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Report Parameters')
                    ->schema([
                        Select::make('reportType')
                            ->label('Report Type')
                            ->options([
                                'income_expense' => 'Income vs Expense Summary',
                                'income_detail' => 'Income Detail Report',
                                'expense_detail' => 'Expense Detail Report',
                                'cash_flow' => 'Cash Flow Summary',
                                'payables' => 'Outstanding Payables',
                            ])
                            ->default('income_expense')
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport()),

                        DatePicker::make('startDate')
                            ->label('Start Date')
                            ->default(now()->startOfMonth())
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport()),

                        DatePicker::make('endDate')
                            ->label('End Date')
                            ->default(now()->endOfMonth())
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport()),
                    ])
                    ->columns(3),
            ]);
    }

    public function generateReport(): void
    {
        $reportService = app(FinancialReportService::class);
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        switch ($this->reportType) {
            case 'income_expense':
                $this->reportData = [
                    'comparison' => $reportService->getIncomeExpenseComparison($start, $end),
                    'income' => $reportService->getIncomeSummary($start, $end),
                    'expenses' => $reportService->getExpenseSummary($start, $end),
                ];
                break;

            case 'income_detail':
                $this->reportData = $reportService->getIncomeSummary($start, $end);
                break;

            case 'expense_detail':
                $this->reportData = $reportService->getExpenseSummary($start, $end);
                break;

            case 'cash_flow':
                $this->reportData = $reportService->getCashFlowSummary($start, $end);
                break;

            case 'payables':
                $this->reportData = $reportService->getOutstandingPayables();
                break;

            default:
                $this->reportData = [];
        }
    }

    public function getReportTitle(): string
    {
        return match ($this->reportType) {
            'income_expense' => 'Income vs Expense Summary',
            'income_detail' => 'Income Detail Report',
            'expense_detail' => 'Expense Detail Report',
            'cash_flow' => 'Cash Flow Summary',
            'payables' => 'Outstanding Payables Report',
            default => 'Financial Report',
        };
    }

    public function getExportUrl(): string
    {
        $routeName = match ($this->reportType) {
            'income_expense' => 'financial-reports.income-expense',
            'income_detail' => 'financial-reports.income-detail',
            'expense_detail' => 'financial-reports.expense-detail',
            'cash_flow' => 'financial-reports.cash-flow',
            'payables' => 'financial-reports.payables',
            default => 'financial-reports.income-expense',
        };

        return route($routeName, [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }
}
