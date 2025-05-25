<?php

namespace App\Services;

use App\Models\StudentFee;
use App\Models\Student;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BalanceForwardService
{
    /**
     * Process overpayment and carry forward to next term
     */
    public function processOverpayment(StudentFee $studentFee, float $overpaymentAmount): array
    {
        DB::beginTransaction();

        try {
            // Find next term for this student
            $nextTerm = $this->getNextTerm($studentFee);

            if (!$nextTerm) {
                // No next term found, create credit balance entry
                $this->createCreditBalance($studentFee, $overpaymentAmount);

                DB::commit();
                return [
                    'success' => true,
                    'message' => "Overpayment of ZMW " . number_format($overpaymentAmount, 2) . " has been recorded as credit balance.",
                    'type' => 'credit_balance'
                ];
            }

            // Check if student fee already exists for next term
            $nextTermFee = $this->getOrCreateNextTermFee($studentFee, $nextTerm);

            // Apply overpayment to next term
            $this->applyBalanceForward($nextTermFee, $overpaymentAmount, $studentFee);

            // Record the transaction
            $this->recordBalanceForwardTransaction($studentFee, $nextTermFee, $overpaymentAmount);

            DB::commit();

            return [
                'success' => true,
                'message' => "Overpayment of ZMW " . number_format($overpaymentAmount, 2) . " has been carried forward to {$nextTerm->name}.",
                'type' => 'balance_forward',
                'next_term' => $nextTerm->name
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Balance forward failed', [
                'student_fee_id' => $studentFee->id,
                'overpayment' => $overpaymentAmount,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Get the next term based on current term sequence
     */
    private function getNextTerm(StudentFee $studentFee): ?Term
    {
        $currentTerm = $studentFee->term;

        if (!$currentTerm) {
            return null;
        }

        // Find next term in the same academic year first
        $nextTerm = Term::where('academic_year_id', $currentTerm->academic_year_id)
            ->where('start_date', '>', $currentTerm->end_date)
            ->orderBy('start_date')
            ->first();

        if ($nextTerm) {
            return $nextTerm;
        }

        // If no next term in current academic year, find first term of next academic year
        $nextAcademicYear = AcademicYear::where('start_date', '>', $currentTerm->academicYear->end_date)
            ->orderBy('start_date')
            ->first();

        if ($nextAcademicYear) {
            return Term::where('academic_year_id', $nextAcademicYear->id)
                ->orderBy('start_date')
                ->first();
        }

        return null;
    }

    /**
     * Get or create student fee record for next term
     */
    private function getOrCreateNextTermFee(StudentFee $currentFee, Term $nextTerm): StudentFee
    {
        // Check if fee record already exists
        $existingFee = StudentFee::where('student_id', $currentFee->student_id)
            ->where('term_id', $nextTerm->id)
            ->where('academic_year_id', $nextTerm->academic_year_id)
            ->first();

        if ($existingFee) {
            return $existingFee;
        }

        // Find appropriate fee structure for next term
        $feeStructure = FeeStructure::where('grade_id', $currentFee->grade_id)
            ->where('term_id', $nextTerm->id)
            ->where('academic_year_id', $nextTerm->academic_year_id)
            ->where('is_active', true)
            ->first();

        if (!$feeStructure) {
            throw new \Exception("No fee structure found for next term: {$nextTerm->name}");
        }

        // Create new student fee record
        return StudentFee::create([
            'student_id' => $currentFee->student_id,
            'fee_structure_id' => $feeStructure->id,
            'academic_year_id' => $nextTerm->academic_year_id,
            'term_id' => $nextTerm->id,
            'grade_id' => $currentFee->grade_id,
            'amount_paid' => 0,
            'balance' => $feeStructure->total_fee,
            'payment_status' => 'unpaid',
            'notes' => 'Auto-created for balance forward from ' . $currentFee->term->name,
        ]);
    }

    /**
     * Apply balance forward to next term fee
     */
    private function applyBalanceForward(StudentFee $nextTermFee, float $forwardAmount, StudentFee $sourceFee): void
    {
        $newAmountPaid = $nextTermFee->amount_paid + $forwardAmount;
        $totalFee = $nextTermFee->feeStructure->total_fee;
        $newBalance = max(0, $totalFee - $newAmountPaid);

        // Determine payment status
        $paymentStatus = 'partial';
        if ($newAmountPaid >= $totalFee) {
            $paymentStatus = 'paid';
        } elseif ($newAmountPaid <= 0) {
            $paymentStatus = 'unpaid';
        }

        $nextTermFee->update([
            'amount_paid' => $newAmountPaid,
            'balance' => $newBalance,
            'payment_status' => $paymentStatus,
            'notes' => ($nextTermFee->notes ?? '') . "\nBalance forward: ZMW " . number_format($forwardAmount, 2) . " from {$sourceFee->term->name}",
        ]);

        // If there's still overpayment, handle it recursively
        if ($newAmountPaid > $totalFee) {
            $remainingOverpayment = $newAmountPaid - $totalFee;
            $this->processOverpayment($nextTermFee, $remainingOverpayment);
        }
    }

    /**
     * Create credit balance entry for overpayments when no next term exists
     */
    private function createCreditBalance(StudentFee $studentFee, float $creditAmount): void
    {
        // You might want to create a separate credit_balances table
        // For now, we'll add it to notes and create a transaction record
        $studentFee->update([
            'notes' => ($studentFee->notes ?? '') . "\nCredit Balance: ZMW " . number_format($creditAmount, 2) . " (Overpayment on " . now()->format('Y-m-d') . ")",
        ]);
    }

    /**
     * Record balance forward transaction
     */
    private function recordBalanceForwardTransaction(StudentFee $sourceFee, StudentFee $targetFee, float $amount): void
    {
        PaymentTransaction::create([
            'student_fee_id' => $targetFee->id,
            'amount' => $amount,
            'type' => 'balance_forward',
            'reference_number' => 'BF-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT),
            'metadata' => [
                'source_fee_id' => $sourceFee->id,
                'source_term' => $sourceFee->term->name,
                'target_term' => $targetFee->term->name,
                'processed_at' => now()->toISOString(),
            ],
            'processed_by' => auth()->id(),
        ]);
    }

    /**
     * Get current active term based on date
     */
    public function getCurrentActiveTerm(): ?Term
    {
        $today = Carbon::today();

        return Term::where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }

    /**
     * Get payment history for a student
     */
    public function getPaymentHistory(Student $student, ?int $academicYearId = null): array
    {
        $query = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure', 'term', 'academicYear', 'paymentTransactions']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $fees = $query->orderBy('created_at', 'desc')->get();

        $history = [];
        foreach ($fees as $fee) {
            $history[] = [
                'term' => $fee->term->name ?? 'Unknown',
                'academic_year' => $fee->academicYear->name ?? 'Unknown',
                'total_fee' => $fee->feeStructure->total_fee ?? 0,
                'amount_paid' => $fee->amount_paid,
                'balance' => $fee->balance,
                'payment_status' => $fee->payment_status,
                'payment_date' => $fee->payment_date,
                'transactions' => $fee->paymentTransactions ?? [],
            ];
        }

        return $history;
    }

    /**
     * Generate comprehensive payment statement
     */
    public function generatePaymentStatement(Student $student, ?int $academicYearId = null): array
    {
        $history = $this->getPaymentHistory($student, $academicYearId);

        $summary = [
            'student_name' => $student->name,
            'student_id' => $student->student_id_number,
            'total_fees_charged' => 0,
            'total_payments_made' => 0,
            'total_outstanding' => 0,
            'credit_balance' => 0,
            'payment_history' => $history,
        ];

        foreach ($history as $record) {
            $summary['total_fees_charged'] += $record['total_fee'];
            $summary['total_payments_made'] += $record['amount_paid'];
            $summary['total_outstanding'] += $record['balance'];
        }

        return $summary;
    }
}
