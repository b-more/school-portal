<?php

namespace App\Console\Commands;

use App\Models\StudentFee;
use App\Models\PaymentTransaction;
use App\Services\SmsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApplyLateFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:apply-late-fees {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply late fees to overdue student payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $today = Carbon::today();

        $this->info('Checking for overdue payments...');

        // Get unpaid and partially paid fees with deadlines that have passed
        $overdueStudentFees = StudentFee::with(['student', 'student.parentGuardian', 'feeStructure'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', $today)
            ->where('is_overdue', false) // Only process fees not yet marked as overdue
            ->get();

        if ($overdueStudentFees->isEmpty()) {
            $this->info('No overdue payments found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdueStudentFees->count()} overdue payments.");

        $processed = 0;
        $skipped = 0;

        foreach ($overdueStudentFees as $studentFee) {
            $feeStructure = $studentFee->feeStructure;

            // Check if late fee is configured
            if (!$feeStructure->late_fee_amount && !$feeStructure->late_fee_percentage) {
                $this->warn("No late fee configured for fee structure ID {$feeStructure->id}. Skipping student fee ID {$studentFee->id}");
                $skipped++;
                continue;
            }

            // Calculate late fee
            $lateFee = $this->calculateLateFee($feeStructure, $studentFee);

            if ($lateFee <= 0) {
                $skipped++;
                continue;
            }

            $this->line("Student: {$studentFee->student->name}, Late Fee: ZMW {$lateFee}");

            if (!$dryRun) {
                // Apply late fee
                $studentFee->update([
                    'late_fee_applied' => $lateFee,
                    'balance' => $studentFee->balance + $lateFee,
                    'is_overdue' => true,
                    'overdue_since' => $today,
                ]);

                // Create transaction record
                PaymentTransaction::create([
                    'student_fee_id' => $studentFee->id,
                    'amount' => $lateFee,
                    'type' => 'adjustment',
                    'notes' => 'Late fee applied for overdue payment',
                    'processed_by' => 1, // System user
                    'transaction_date' => $today,
                ]);

                // Send SMS notification if parent exists
                if ($studentFee->student->parentGuardian && $studentFee->student->parentGuardian->phone) {
                    $this->sendLateFeeNotification($studentFee, $lateFee);
                }

                Log::info('Late fee applied', [
                    'student_fee_id' => $studentFee->id,
                    'student_id' => $studentFee->student_id,
                    'late_fee' => $lateFee,
                    'new_balance' => $studentFee->balance,
                ]);

                $processed++;
            }
        }

        if ($dryRun) {
            $this->info("DRY RUN: Would apply late fees to {$overdueStudentFees->count()} payments.");
        } else {
            $this->info("Successfully applied late fees to {$processed} payments. Skipped: {$skipped}");
        }

        return Command::SUCCESS;
    }

    /**
     * Calculate late fee based on fee structure configuration
     */
    private function calculateLateFee($feeStructure, $studentFee): float
    {
        $lateFee = 0;

        // Apply fixed amount if configured
        if ($feeStructure->late_fee_amount) {
            $lateFee += (float) $feeStructure->late_fee_amount;
        }

        // Apply percentage if configured
        if ($feeStructure->late_fee_percentage) {
            $percentageFee = ($feeStructure->total_fee * $feeStructure->late_fee_percentage) / 100;
            $lateFee += $percentageFee;
        }

        return round($lateFee, 2);
    }

    /**
     * Send late fee notification SMS
     */
    private function sendLateFeeNotification(StudentFee $studentFee, float $lateFee): void
    {
        try {
            $parent = $studentFee->student->parentGuardian;
            $student = $studentFee->student;

            $message = "Dear {$parent->name}, a late fee of ZMW " . number_format($lateFee, 2);
            $message .= " has been applied to {$student->name}'s account due to overdue payment. ";
            $message .= "New balance: ZMW " . number_format($studentFee->balance, 2) . ". ";
            $message .= "Please clear the balance as soon as possible. Thank you.";

            $smsService = app(SmsService::class);
            $smsService->send(
                $message,
                $parent->phone,
                'late_fee_notification',
                $studentFee->id
            );

        } catch (\Exception $e) {
            Log::error('Failed to send late fee SMS', [
                'student_fee_id' => $studentFee->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
