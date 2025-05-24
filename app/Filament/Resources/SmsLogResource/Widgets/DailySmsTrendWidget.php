<?php

namespace App\Filament\Resources\SmsLogResource\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class DailySmsTrendWidget extends ChartWidget
{
    protected static ?string $heading = 'Daily SMS Trend (Last 14 Days)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get daily SMS counts for the last 14 days
        $dates = collect();
        $sentCounts = collect();
        $failedCounts = collect();

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('M d');
            $dates->push($date);

            $day = Carbon::now()->subDays($i);

            $sentCount = SmsLog::whereDate('created_at', $day)
                ->where(function($query) {
                    $query->where('status', 'sent')
                        ->orWhere('status', 'delivered');
                })
                ->count();
            $sentCounts->push($sentCount);

            $failedCount = SmsLog::whereDate('created_at', $day)
                ->where('status', 'failed')
                ->count();
            $failedCounts->push($failedCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Sent SMS',
                    'data' => $sentCounts->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Failed SMS',
                    'data' => $failedCounts->toArray(),
                    'backgroundColor' => 'rgba(244, 63, 94, 0.5)',
                    'borderColor' => 'rgb(244, 63, 94)',
                ],
            ],
            'labels' => $dates->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
