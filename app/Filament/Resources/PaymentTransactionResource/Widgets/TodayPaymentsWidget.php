<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use App\Constants\RoleConstants;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TodayPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Hide from students
        if (!$user || $user->role_id === RoleConstants::STUDENT) {
            return [];
        }

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
        $currentYear = AcademicYear::where('is_current', true)->first();

        return [
            Stat::make('Today\'s Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description($transactionCount . ' payment' . ($transactionCount !== 1 ? 's' : '') . ' received today')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getTodayHourlyData())
                ->extraAttributes([
                    'class' => 'relative',
                ]),
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
