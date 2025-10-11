<?php

namespace App\Filament\Resources\PaymentTransactionResource\Widgets;

use App\Models\PaymentTransaction;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Student;
use App\Constants\RoleConstants;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Term1PaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();

        // Only show for students
        if (!$user || $user->role_id !== RoleConstants::STUDENT) {
            return [];
        }

        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            return [];
        }

        $currentYear = AcademicYear::where('is_current', true)->first();
        $term1 = Term::where('name', 'Term 1')
            ->orWhere('name', 'First Term')
            ->first();

        if (!$term1 || !$currentYear) {
            return [
                Stat::make('Term 1 Payments', 'No Data')
                    ->description('Term 1 or Academic Year not found')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        $query = PaymentTransaction::whereHas('studentFee', function ($q) use ($student, $term1, $currentYear) {
            $q->where('student_id', $student->id)
              ->whereHas('feeStructure', function ($fq) use ($term1, $currentYear) {
                  $fq->where('term_id', $term1->id)
                     ->where('academic_year_id', $currentYear->id);
              });
        })->where('type', 'payment');

        $totalAmount = $query->sum('amount');
        $transactionCount = $query->count();

        // Get expected fee for this student in Term 1
        $studentFee = \App\Models\StudentFee::where('student_id', $student->id)
            ->whereHas('feeStructure', function ($q) use ($term1, $currentYear) {
                $q->where('term_id', $term1->id)
                  ->where('academic_year_id', $currentYear->id);
            })
            ->first();

        $expectedFee = $studentFee->feeStructure->total_fee ?? 0;
        $balance = $studentFee->balance ?? ($expectedFee - $totalAmount);
        $paymentProgress = $expectedFee > 0 ? ($totalAmount / $expectedFee) * 100 : 0;

        return [
            Stat::make('Term 1 - ' . $student->grade->name, 'ZMW ' . number_format($totalAmount, 2))
                ->description(number_format($paymentProgress, 1) . '% paid • Balance: ZMW ' . number_format(max(0, $balance), 2))
                ->descriptionIcon($balance <= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-banknotes')
                ->color($balance <= 0 ? 'success' : ($paymentProgress >= 50 ? 'info' : 'warning'))
                ->extraAttributes([
                    'class' => 'relative',
                ]),
        ];
    }
}
