<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class MonthPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $currentYear = AcademicYear::where('is_current', true)->first();

        $query = PaymentTransaction::whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->where('type', 'payment');

        if ($currentYear) {
            $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $totalAmount = $query->sum('amount');
        $transactionCount = $query->count();

        // Get last month's data for comparison
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $lastMonthQuery = PaymentTransaction::whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->where('type', 'payment');

        if ($currentYear) {
            $lastMonthQuery->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $lastMonthAmount = $lastMonthQuery->sum('amount');
        $percentageChange = $lastMonthAmount > 0
            ? (($totalAmount - $lastMonthAmount) / $lastMonthAmount) * 100
            : 0;

        return [
            Stat::make('This Month\'s Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description(
                    abs($percentageChange) > 0
                        ? number_format(abs($percentageChange), 1) . '% ' . ($percentageChange >= 0 ? 'increase' : 'decrease') . ' from last month'
                        : 'No change from last month'
                )
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->chart($this->getMonthlyWeeklyData()),

            Stat::make('Monthly Transactions', $transactionCount)
                ->description('Payments this month')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Daily Average', 'ZMW ' . number_format($totalAmount / Carbon::now()->day, 2))
                ->description('Average per day this month')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }

    protected function getMonthlyWeeklyData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $weeklyData = [];
        for ($i = 0; $i < 4; $i++) {
            $weekStart = $startOfMonth->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->addWeek();

            $query = PaymentTransaction::whereBetween('transaction_date', [$weekStart, $weekEnd])
                ->where('type', 'payment');

            if ($currentYear) {
                $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                    $q->where('id', $currentYear->id);
                });
            }

            $weeklyData[] = $query->sum('amount');
        }

        return $weeklyData;
    }
}
