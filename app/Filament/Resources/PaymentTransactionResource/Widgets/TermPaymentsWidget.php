<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use App\Models\Term;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class TermPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
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

        return [
            Stat::make('This Term\'s Payments', 'ZMW ' . number_format($totalAmount, 2))
                ->description($currentTerm->name . ' - ' . number_format($collectionRate, 1) . '% of expected fees')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getTermMonthlyData()),

            Stat::make('Term Transactions', $transactionCount)
                ->description('Total payments this term')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Expected Fees', 'ZMW ' . number_format($expectedFees, 2))
                ->description('Outstanding: ZMW ' . number_format(max(0, $expectedFees - $totalAmount), 2))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
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
