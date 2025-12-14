<?php

namespace App\Filament\Resources\StudentFeeResource\Widgets;

use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Term;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentFeeStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        if (! $currentAcademicYear) {
            return [
                Stat::make('No Active Academic Year', 'Please set an active academic year')
                    ->color('danger'),
            ];
        }

        // Get current term
        $currentTerm = Term::where('academic_year_id', $currentAcademicYear->id)
            ->where('is_current', true)
            ->first();

        $termName = $currentTerm ? $currentTerm->name : 'No active term';

        // Calculate total expected fees based on active students and fee structures
        // This calculates what SHOULD be collected from all active students
        $totalExpectedFees = $this->calculateExpectedFees($currentAcademicYear->id, $currentTerm?->id);

        // Get statistics from actual StudentFee records for current academic year
        $totalFeeRecords = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
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

        // Count active students
        $totalActiveStudents = Student::where('enrollment_status', 'active')->count();

        // Students with fee records
        $studentsWithFees = StudentFee::where('student_fees.academic_year_id', $currentAcademicYear->id)
            ->distinct('student_id')
            ->count('student_id');

        // Calculate collection rate based on expected fees
        $collectionRate = $totalExpectedFees > 0 ? round(($totalPaid / $totalExpectedFees) * 100, 1) : 0;

        // Outstanding = Expected - Paid
        $totalOutstanding = $totalExpectedFees - $totalPaid;

        return [
            Stat::make('Total Expected Fees', 'ZMW '.number_format($totalExpectedFees, 2))
                ->description("{$currentAcademicYear->name} - {$termName} ({$totalActiveStudents} active students)")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Total Collected', 'ZMW '.number_format($totalPaid, 2))
                ->description("Collection Rate: {$collectionRate}%")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Outstanding Balance', 'ZMW '.number_format(max(0, $totalOutstanding), 2))
                ->description('Amount pending collection')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalOutstanding > 0 ? 'warning' : 'success'),

            Stat::make('Fully Paid Students', $paidCount)
                ->description("Out of {$studentsWithFees} with fee records")
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

    /**
     * Calculate total expected fees based on active students and their grade fee structures
     */
    protected function calculateExpectedFees(int $academicYearId, ?int $termId): float
    {
        if (!$termId) {
            return 0;
        }

        // Get all active students grouped by grade with their fee structure amounts
        $expectedFees = DB::table('students')
            ->join('fee_structures', function ($join) use ($academicYearId, $termId) {
                $join->on('students.grade_id', '=', 'fee_structures.grade_id')
                    ->where('fee_structures.academic_year_id', '=', $academicYearId)
                    ->where('fee_structures.term_id', '=', $termId)
                    ->where('fee_structures.is_active', '=', true);
            })
            ->where('students.enrollment_status', '=', 'active')
            ->sum('fee_structures.total_fee');

        return (float) $expectedFees;
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh every 30 seconds
    }
}
