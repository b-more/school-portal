<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class TodayPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $currentYear = AcademicYear::where('is_current', true)->first();

        $query = PaymentTransaction::whereDate('transaction_date', $today)
            ->where('type', 'payment');

        if ($currentYear) {
            $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $totalAmount = $query->sum('amount');
        $transactionCount = $query->count();
        $averageAmount = $transactionCount > 0 ? $totalAmount / $transactionCount : 0;

        return [
            Stat::make('Today\'s Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description($transactionCount . ' transaction' . ($transactionCount !== 1 ? 's' : ''))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getTodayHourlyData()),

            Stat::make('Average Payment', 'ZMW ' . number_format($averageAmount, 2))
                ->description('Per transaction today')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Transactions Today', $transactionCount)
                ->description('Payments recorded')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }

    protected function getTodayHourlyData(): array
    {
        $today = Carbon::today();
        $currentYear = AcademicYear::where('is_current', true)->first();

        $hourlyData = [];
        for ($i = 0; $i < 24; $i++) {
            $startHour = $today->copy()->setHour($i)->setMinute(0)->setSecond(0);
            $endHour = $startHour->copy()->addHour();

            $query = PaymentTransaction::whereBetween('transaction_date', [$startHour, $endHour])
                ->where('type', 'payment');

            if ($currentYear) {
                $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                    $q->where('id', $currentYear->id);
                });
            }

            $hourlyData[] = $query->sum('amount');
        }

        return array_slice($hourlyData, max(0, Carbon::now()->hour - 6), 7);
    }
}
