<?php

namespace App\Filament\Resources\SmsLogResource\Widgets;

use App\Models\SmsLog;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class SmsTypeDistributionWidget extends ChartWidget
{
    protected static ?string $heading = 'SMS Distribution by Type';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // Get distribution by message type for the current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $typeDistribution = SmsLog::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->select('message_type', \DB::raw('count(*) as total'))
            ->groupBy('message_type')
            ->orderBy('total', 'desc')
            ->get();

        $labels = [];
        $data = [];

        foreach ($typeDistribution as $item) {
            switch ($item->message_type) {
                case 'homework_notification':
                    $labels[] = 'Homework';
                    break;
                case 'result_notification':
                    $labels[] = 'Results';
                    break;
                case 'fee_reminder':
                    $labels[] = 'Fee Reminder';
                    break;
                case 'event_notification':
                    $labels[] = 'Events';
                    break;
                case 'general':
                    $labels[] = 'General';
                    break;
                case 'other':
                    $labels[] = 'Other';
                    break;
                default:
                    $labels[] = ucfirst($item->message_type);
            }

            $data[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'SMS by Type',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(54, 162, 235)',
                        'rgb(255, 99, 132)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                        'rgb(255, 159, 64)',
                        'rgb(201, 203, 207)',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
