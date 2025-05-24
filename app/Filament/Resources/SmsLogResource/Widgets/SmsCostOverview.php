<?php

namespace App\Filament\Resources\SmsLogResource\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class SmsCostOverview extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        // Calculate costs for different time periods
        $todayCost = SmsLog::whereDate('created_at', Carbon::today())
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->sum('cost');

        $thisMonthCost = SmsLog::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->sum('cost');

        $lastMonthCost = SmsLog::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->sum('cost');

        $yearToDateCost = SmsLog::whereYear('created_at', Carbon::now()->year)
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->sum('cost');

        // Calculate percent change from last month
        $percentChange = $lastMonthCost > 0
            ? round((($thisMonthCost - $lastMonthCost) / $lastMonthCost) * 100, 1)
            : 100;

        return [
            Stat::make('Today\'s Cost', 'ZMW ' . number_format($todayCost, 2))
                ->description('Daily SMS expenses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('This Month', 'ZMW ' . number_format($thisMonthCost, 2))
                ->description($percentChange >= 0 ? "+$percentChange% from last month" : "$percentChange% from last month")
                ->descriptionIcon($percentChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentChange > 20 ? 'warning' : 'success'),

            Stat::make('Year to Date', 'ZMW ' . number_format($yearToDateCost, 2))
                ->description('Total SMS expenses YTD')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color('info'),
        ];
    }
}
