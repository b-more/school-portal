<?php

namespace App\Filament\Resources\SmsLogResource\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SmsDashboardWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get daily counts
        $today = Carbon::today();
        $dailyTotal = SmsLog::whereDate('created_at', $today)->count();
        $dailySent = SmsLog::whereDate('created_at', $today)
            ->where('status', 'sent')
            ->orWhere('status', 'delivered')
            ->whereDate('created_at', $today)
            ->count();
        $dailyFailed = SmsLog::whereDate('created_at', $today)
            ->where('status', 'failed')
            ->count();
        $dailyPending = SmsLog::whereDate('created_at', $today)
            ->where('status', 'pending')
            ->count();

        $dailySuccessRate = $dailyTotal > 0 ? round(($dailySent / $dailyTotal) * 100) : 0;

        // Get weekly counts
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyTotal = SmsLog::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        $weeklySent = SmsLog::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->count();
        $weeklyFailed = SmsLog::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'failed')
            ->count();
        $weeklyPending = SmsLog::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', 'pending')
            ->count();

        $weeklySuccessRate = $weeklyTotal > 0 ? round(($weeklySent / $weeklyTotal) * 100) : 0;

        // Get monthly counts
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyTotal = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $monthlySent = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->count();
        $monthlyFailed = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'failed')
            ->count();
        $monthlyPending = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'pending')
            ->count();

        $monthlySuccessRate = $monthlyTotal > 0 ? round(($monthlySent / $monthlyTotal) * 100) : 0;

        // Calculate monthly cost
        $monthlyCost = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where(function($query) {
                $query->where('status', 'sent')
                    ->orWhere('status', 'delivered');
            })
            ->sum('cost');

        return [
            Stat::make('Today', "$dailySent/$dailyTotal SMS")
                ->description("$dailySuccessRate% Success Rate")
                ->descriptionIcon($dailySuccessRate >= 90 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($dailySuccessRate >= 90 ? 'success' : ($dailySuccessRate >= 70 ? 'warning' : 'danger'))
                ->chart([
                    $dailySent,
                    $dailyFailed,
                    $dailyPending
                ]),

            Stat::make('This Week', "$weeklySent/$weeklyTotal SMS")
                ->description("$weeklySuccessRate% Success Rate")
                ->descriptionIcon($weeklySuccessRate >= 90 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-circle')
                ->color($weeklySuccessRate >= 90 ? 'success' : ($weeklySuccessRate >= 70 ? 'warning' : 'danger'))
                ->chart([
                    $weeklySent,
                    $weeklyFailed,
                    $weeklyPending
                ]),

            Stat::make('This Month', "$monthlySent/$monthlyTotal SMS")
                ->description("ZMW " . number_format($monthlyCost, 2) . " Total Cost")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info')
                ->chart([
                    $monthlySent,
                    $monthlyFailed,
                    $monthlyPending
                ]),
        ];
    }
}
