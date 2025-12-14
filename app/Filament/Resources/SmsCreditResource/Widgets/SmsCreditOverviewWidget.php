<?php

namespace App\Filament\Resources\SmsCreditResource\Widgets;

use App\Models\SmsCredit;
use App\Models\SmsCreditTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SmsCreditOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $credit = SmsCredit::first();

        if (!$credit) {
            return [
                Stat::make('SMS Credit Balance', 'Not Configured')
                    ->description('Please configure SMS credits')
                    ->color('danger'),
            ];
        }

        // Calculate statistics
        $todaySpent = SmsCreditTransaction::where('type', 'debit')
            ->whereDate('created_at', today())
            ->sum('amount');

        $monthSpent = SmsCreditTransaction::where('type', 'debit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $monthCredits = SmsCreditTransaction::where('type', 'credit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $todaySmsCount = SmsCreditTransaction::where('type', 'debit')
            ->whereDate('created_at', today())
            ->count();

        $monthSmsCount = SmsCreditTransaction::where('type', 'debit')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $estimatedRemaining = $credit->cost_per_sms > 0
            ? floor($credit->balance / $credit->cost_per_sms)
            : 0;

        // Balance color based on threshold
        $balanceColor = 'success';
        if ($credit->balance <= 0) {
            $balanceColor = 'danger';
        } elseif ($credit->isBalanceLow()) {
            $balanceColor = 'warning';
        }

        // Status indicator
        $statusIcon = $credit->is_active ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';
        $statusColor = $credit->is_active ? 'success' : 'danger';
        $statusText = $credit->is_active ? 'Active' : 'Disabled';

        return [
            Stat::make('Current Balance', number_format($credit->balance) . ' credits')
                ->description($credit->isBalanceLow() ? 'Balance is low!' : 'SMS sending is ' . $statusText)
                ->descriptionIcon($credit->isBalanceLow() ? 'heroicon-o-exclamation-triangle' : $statusIcon)
                ->color($balanceColor)
                ->chart($this->getBalanceChart()),

            Stat::make('Estimated SMS Remaining', number_format($estimatedRemaining))
                ->description($credit->cost_per_sms . ' credit(s) per SMS')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color($estimatedRemaining < 100 ? 'warning' : 'success'),

            Stat::make('Today\'s Usage', number_format($todaySpent) . ' credits')
                ->description($todaySmsCount . ' SMS sent today')
                ->descriptionIcon('heroicon-o-paper-airplane')
                ->color('info'),

            Stat::make('This Month', number_format($monthSpent) . ' credits used')
                ->description($monthSmsCount . ' SMS | ' . number_format($monthCredits) . ' credited')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('gray'),
        ];
    }

    protected function getBalanceChart(): array
    {
        // Get last 7 days of balance
        $transactions = SmsCreditTransaction::where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at')
            ->get();

        if ($transactions->isEmpty()) {
            return [0, 0, 0, 0, 0, 0, 0];
        }

        $chart = [];
        $balance = $transactions->first()->balance_before ?? 0;

        foreach ($transactions as $transaction) {
            $chart[] = (int) $transaction->balance_after;
        }

        // Ensure we have at least 7 data points
        while (count($chart) < 7) {
            array_unshift($chart, $chart[0] ?? 0);
        }

        return array_slice($chart, -7);
    }
}
