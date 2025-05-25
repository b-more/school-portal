<?php

namespace App\Filament\Resources\StudentFeeResource\Widgets;

use App\Models\StudentFee;
use App\Models\AcademicYear;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentFeeStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (!$currentAcademicYear) {
            return [
                Stat::make('No Active Academic Year', 'Please set an active academic year')
                    ->color('danger'),
            ];
        }

        // Get statistics for current academic year
        $totalFees = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->join('fee_structures', 'student_fees.fee_structure_id', '=', 'fee_structures.id')
            ->sum('fee_structures.total_fee');

        $totalPaid = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->sum('amount_paid');

        $totalBalance = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->sum('balance');

        $paidCount = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->where('payment_status', 'paid')
            ->count();

        $partialCount = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->where('payment_status', 'partial')
            ->count();

        $unpaidCount = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->where('payment_status', 'unpaid')
            ->count();

        $totalStudents = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->distinct('student_id')
            ->count();

        // Calculate collection rate
        $collectionRate = $totalFees > 0 ? round(($totalPaid / $totalFees) * 100, 1) : 0;

        return [
            Stat::make('Total Expected Fees', 'ZMW ' . number_format($totalFees, 2))
                ->description($currentAcademicYear->name)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Total Collected', 'ZMW ' . number_format($totalPaid, 2))
                ->description("Collection Rate: {$collectionRate}%")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Outstanding Balance', 'ZMW ' . number_format($totalBalance, 2))
                ->description('Amount pending collection')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalBalance > 0 ? 'warning' : 'success'),

            Stat::make('Fully Paid Students', $paidCount)
                ->description("Out of {$totalStudents} total students")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Partially Paid', $partialCount)
                ->description('Students with partial payments')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Unpaid Students', $unpaidCount)
                ->description('Students with no payments')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
    }
}
