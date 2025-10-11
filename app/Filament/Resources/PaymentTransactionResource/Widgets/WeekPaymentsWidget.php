<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class WeekPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $currentYear = AcademicYear::where('is_current', true)->first();

        $query = PaymentTransaction::whereBetween('transaction_date', [$startOfWeek, $endOfWeek])
            ->where('type', 'payment');

        if ($currentYear) {
            $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $totalAmount = $query->sum('amount');
        $transactionCount = $query->count();

        // Get last week's data for comparison
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $lastWeekQuery = PaymentTransaction::whereBetween('transaction_date', [$lastWeekStart, $lastWeekEnd])
            ->where('type', 'payment');

        if ($currentYear) {
            $lastWeekQuery->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $lastWeekAmount = $lastWeekQuery->sum('amount');
        $percentageChange = $lastWeekAmount > 0
            ? (($totalAmount - $lastWeekAmount) / $lastWeekAmount) * 100
            : 0;

        return [
            Stat::make('This Week\'s Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description(
                    abs($percentageChange) > 0
                        ? number_format(abs($percentageChange), 1) . '% ' . ($percentageChange >= 0 ? 'increase' : 'decrease') . ' from last week'
                        : 'No change from last week'
                )
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->chart($this->getWeeklyDailyData()),

            Stat::make('Weekly Transactions', $transactionCount)
                ->description('Payments this week')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Daily Average', 'ZMW ' . number_format($totalAmount / 7, 2))
                ->description('Average per day this week')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),
        ];
    }

    protected function getWeeklyDailyData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $dailyData = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $nextDay = $day->copy()->addDay();

            $query = PaymentTransaction::whereBetween('transaction_date', [$day, $nextDay])
                ->where('type', 'payment');

            if ($currentYear) {
                $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                    $q->where('id', $currentYear->id);
                });
            }

            $dailyData[] = $query->sum('amount');
        }

        return $dailyData;
    }
}
