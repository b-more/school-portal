<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use App\Models\Accounting\Expense;
use App\Models\Accounting\IncomeRecord;
use App\Models\Accounting\BankAccount;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FinancialReportService
{
    /**
     * Get income summary for a date range
     */
    public function getIncomeSummary($startDate, $endDate): array
    {
        $incomes = IncomeRecord::with('account')
            ->whereBetween('income_date', [$startDate, $endDate])
            ->get();

        $byAccount = $incomes->groupBy('account_id')->map(function ($group) {
            return [
                'name' => $group->first()->account->name ?? 'Unknown',
                'amount' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        });

        $byPaymentMethod = $incomes->groupBy('payment_method')->map(function ($group) {
            return [
                'method' => $group->first()->payment_method,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        });

        return [
            'total' => $incomes->sum('amount'),
            'total_income' => $incomes->sum('amount'),
            'by_account' => $byAccount->values()->toArray(),
            'by_payment_method' => $byPaymentMethod->values()->toArray(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get expense summary for a date range
     */
    public function getExpenseSummary($startDate, $endDate): array
    {
        $expenses = Expense::with(['category', 'vendor'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($group) {
            return [
                'name' => $group->first()->category->name ?? 'Uncategorized',
                'amount' => $group->sum('total_amount'),
                'count' => $group->count(),
            ];
        });

        $byVendor = $expenses->groupBy('vendor_id')->map(function ($group) {
            return [
                'name' => $group->first()->vendor->name ?? 'N/A',
                'amount' => $group->sum('total_amount'),
                'count' => $group->count(),
            ];
        });

        $byStatus = $expenses->groupBy('payment_status')->map(function ($group) {
            return [
                'status' => $group->first()->payment_status,
                'total' => $group->sum('total_amount'),
                'count' => $group->count(),
            ];
        });

        return [
            'total' => $expenses->sum('total_amount'),
            'total_expenses' => $expenses->sum('total_amount'),
            'total_paid' => $expenses->sum('amount_paid'),
            'total_unpaid' => $expenses->sum('total_amount') - $expenses->sum('amount_paid'),
            'by_category' => $byCategory->values()->toArray(),
            'by_vendor' => $byVendor->values()->toArray(),
            'by_status' => $byStatus->values()->toArray(),
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get income vs expense comparison
     */
    public function getIncomeExpenseComparison($startDate, $endDate): array
    {
        $income = IncomeRecord::whereBetween('income_date', [$startDate, $endDate])
            ->sum('amount');

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('total_amount');

        return [
            'total_income' => $income,
            'total_expenses' => $expenses,
            'net_income' => $income - $expenses,
            'profit_margin' => $income > 0 ? round((($income - $expenses) / $income) * 100, 2) : 0,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get monthly trend data
     */
    public function getMonthlyTrend(int $year): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $income = IncomeRecord::whereBetween('income_date', [$startDate, $endDate])
                ->sum('amount');

            $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('total_amount');

            $months[] = [
                'month' => $startDate->format('M'),
                'month_number' => $month,
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];
        }

        return [
            'year' => $year,
            'months' => $months,
            'total_income' => collect($months)->sum('income'),
            'total_expenses' => collect($months)->sum('expenses'),
            'total_net' => collect($months)->sum('net'),
        ];
    }

    /**
     * Get bank account balances
     */
    public function getBankBalances(): array
    {
        $accounts = BankAccount::where('is_active', true)->get();

        return $accounts->map(function ($account) {
            return [
                'id' => $account->id,
                'name' => $account->account_name,
                'bank_name' => $account->bank_name,
                'account_name' => $account->account_name,
                'account_number' => $account->account_number ?? '',
                'currency' => $account->currency ?? 'ZMW',
                'balance' => $account->current_balance ?? 0,
                'current_balance' => $account->current_balance ?? 0,
                'is_default' => $account->is_default ?? false,
            ];
        })->toArray();
    }

    /**
     * Get cash flow summary
     */
    public function getCashFlowSummary($startDate, $endDate): array
    {
        // Opening balance (sum of all bank accounts at start date)
        $bankAccounts = BankAccount::where('is_active', true)->get();
        $openingBalance = $bankAccounts->sum('opening_balance');

        // Cash inflows
        $feeIncome = IncomeRecord::whereBetween('income_date', [$startDate, $endDate])
            ->whereHas('account', fn($q) => $q->where('code', 'like', '400%'))
            ->sum('amount');

        $otherIncome = IncomeRecord::whereBetween('income_date', [$startDate, $endDate])
            ->whereHas('account', fn($q) => $q->where('code', 'not like', '400%'))
            ->sum('amount');

        // Cash outflows
        $salaryExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereHas('category', fn($q) => $q->where('code', 'like', 'SAL%'))
            ->where('payment_status', 'paid')
            ->sum('amount_paid');

        $operatingExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->whereHas('category', fn($q) => $q->where('code', 'not like', 'SAL%'))
            ->where('payment_status', 'paid')
            ->sum('amount_paid');

        $totalInflows = $feeIncome + $otherIncome;
        $totalOutflows = $salaryExpenses + $operatingExpenses;
        $netCashFlow = $totalInflows - $totalOutflows;
        $closingBalance = $openingBalance + $netCashFlow;

        return [
            'opening_balance' => $openingBalance,
            'inflows' => [
                'fee_income' => $feeIncome,
                'other_income' => $otherIncome,
                'total' => $totalInflows,
            ],
            'outflows' => [
                'salary_expenses' => $salaryExpenses,
                'operating_expenses' => $operatingExpenses,
                'total' => $totalOutflows,
            ],
            'net_cash_flow' => $netCashFlow,
            'closing_balance' => $closingBalance,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ];
    }

    /**
     * Get top expenses by category
     */
    public function getTopExpenseCategories($startDate, $endDate, int $limit = 10): array
    {
        return Expense::with('category')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->selectRaw('expense_category_id, SUM(total_amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name ?? 'Unknown',
                    'total' => $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Get outstanding payables
     */
    public function getOutstandingPayables(): array
    {
        $expenses = Expense::with('vendor')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->orderBy('expense_date')
            ->get();

        return [
            'total_outstanding' => $expenses->sum(fn($e) => $e->total_amount - $e->amount_paid),
            'count' => $expenses->count(),
            'items' => $expenses->map(function ($expense) {
                return [
                    'expense_number' => $expense->expense_number,
                    'date' => $expense->expense_date,
                    'vendor' => $expense->vendor->name ?? 'N/A',
                    'description' => $expense->description,
                    'total' => $expense->total_amount,
                    'paid' => $expense->amount_paid,
                    'balance' => $expense->total_amount - $expense->amount_paid,
                ];
            })->toArray(),
        ];
    }
}
