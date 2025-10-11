<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Constants\RoleConstants;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TermPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Hide from students
        if (!$user || $user->role_id === RoleConstants::STUDENT) {
            return [];
        }

        $currentTerm = Term::where('is_current', true)->first();
        $currentYear = AcademicYear::where('is_current', true)->first();

        if (!$currentTerm) {
            return [
                Stat::make('Term Payments', 'No Current Term')
                    ->description('Please set a current term in the system')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $termStart = $currentTerm->start_date;
        $termEnd = $currentTerm->end_date ?? Carbon::now();

        $query = PaymentTransaction::whereBetween('transaction_date', [$termStart, $termEnd])
            ->where('type', 'payment')
            ->whereHas('studentFee.feeStructure', function ($q) use ($currentTerm) {
                $q->where('term_id', $currentTerm->id);
            });

        if ($currentYear) {
            $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                $q->where('id', $currentYear->id);
            });
        }

        $totalAmount = $query->sum('amount');
        $transactionCount = $query->count();

        // Get expected total fees for the term
        $expectedFees = \App\Models\FeeStructure::where('fee_structures.term_id', $currentTerm->id)
            ->when($currentYear, function ($q) use ($currentYear) {
                $q->where('fee_structures.academic_year_id', $currentYear->id);
            })
            ->join('student_fees', 'fee_structures.id', '=', 'student_fees.fee_structure_id')
            ->sum('fee_structures.total_fee');

        $collectionRate = $expectedFees > 0
            ? ($totalAmount / $expectedFees) * 100
            : 0;

        // Calculate days elapsed and total days
        $daysElapsed = Carbon::now()->diffInDays($termStart);
        $totalDays = $termEnd->diffInDays($termStart);
        $progressPercentage = $totalDays > 0 ? ($daysElapsed / $totalDays) * 100 : 0;

        $outstanding = max(0, $expectedFees - $totalAmount);

        return [
            Stat::make($currentTerm->name . ' Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description(number_format($collectionRate, 1) . '% collection rate • Outstanding: ZMW ' . number_format($outstanding, 2))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($collectionRate >= 75 ? 'success' : ($collectionRate >= 50 ? 'warning' : 'danger'))
                ->chart($this->getTermMonthlyData())
                ->extraAttributes([
                    'class' => 'relative',
                ]),
        ];
    }

    protected function getTermMonthlyData(): array
    {
        $currentTerm = Term::where('is_current', true)->first();
        $currentYear = AcademicYear::where('is_current', true)->first();

        if (!$currentTerm) {
            return [];
        }

        $termStart = $currentTerm->start_date;
        $termEnd = $currentTerm->end_date ?? Carbon::now();

        $monthlyData = [];
        $currentDate = $termStart->copy();

        while ($currentDate->lessThanOrEqualTo($termEnd)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();

            $query = PaymentTransaction::whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->where('type', 'payment')
                ->whereHas('studentFee.feeStructure', function ($q) use ($currentTerm) {
                    $q->where('term_id', $currentTerm->id);
                });

            if ($currentYear) {
                $query->whereHas('studentFee.feeStructure.academicYear', function ($q) use ($currentYear) {
                    $q->where('id', $currentYear->id);
                });
            }

            $monthlyData[] = $query->sum('amount');
            $currentDate->addMonth();
        }

        return $monthlyData;
    }
}
