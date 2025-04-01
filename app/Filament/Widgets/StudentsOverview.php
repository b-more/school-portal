<?php

namespace App\Filament\Resources\SmsLogResource\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SmsCostOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get metrics for different periods
        $todayCost = SmsLog::whereDate('created_at', Carbon::today())->sum('cost');
        $weekCost = SmsLog::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('cost');
        $monthCost = SmsLog::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->sum('cost');

        // Get counts by status
        $totalSent = SmsLog::getCountByStatus('sent');
        $totalDelivered = SmsLog::getCountByStatus('delivered');
        $totalFailed = SmsLog::getCountByStatus('failed');

        return [
            Stat::make('Total SMS Cost (Today)', 'ZMW ' . number_format($todayCost, 2))
                ->description('For ' . Carbon::today()->format('d M Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total SMS Cost (This Week)', 'ZMW ' . number_format($weekCost, 2))
                ->description('Week ' . Carbon::now()->weekOfYear)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Total SMS Cost (This Month)', 'ZMW ' . number_format($monthCost, 2))
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Total SMS Messages', SmsLog::count())
                ->description("Sent: $totalSent, Delivered: $totalDelivered, Failed: $totalFailed")
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info'),
        ];
    }
}
