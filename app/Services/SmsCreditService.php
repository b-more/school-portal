<?php

namespace App\Services;

use App\Models\SmsCredit;
use App\Models\SmsCreditTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SmsCreditService
{
    protected SmsCredit $credit;

    public function __construct()
    {
        $this->credit = SmsCredit::getInstance();
    }

    /**
     * Get current balance (in credits).
     */
    public function getBalance(): int
    {
        return (int) $this->credit->balance;
    }

    /**
     * Get credit instance.
     */
    public function getCredit(): SmsCredit
    {
        return $this->credit->fresh();
    }

    /**
     * Calculate cost for a message.
     */
    public function calculateCost(string $message): array
    {
        return $this->credit->calculateMessageCost($message);
    }

    /**
     * Calculate message parts.
     */
    public function calculateParts(string $message): int
    {
        return $this->credit->calculateMessageParts($message);
    }

    /**
     * Check if SMS sending is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->credit->is_active;
    }

    /**
     * Check if there's sufficient balance for a message.
     */
    public function hasSufficientBalance(string $message): bool
    {
        return $this->credit->hasSufficientBalance($message);
    }

    /**
     * Check if balance allows sending (considers allow_negative_balance setting).
     */
    public function canSend(string $message): array
    {
        $credit = $this->credit->fresh();

        if (!$credit->is_active) {
            return [
                'allowed' => false,
                'reason' => 'SMS service is currently disabled.',
                'balance' => $credit->balance,
                'cost' => 0,
            ];
        }

        $costDetails = $credit->calculateMessageCost($message);

        if (!$credit->allow_negative_balance && $credit->balance < $costDetails['total_cost']) {
            return [
                'allowed' => false,
                'reason' => 'Insufficient SMS credit balance.',
                'balance' => $credit->balance,
                'cost' => $costDetails['total_cost'],
                'shortage' => $costDetails['total_cost'] - $credit->balance,
                'parts' => $costDetails['parts'],
            ];
        }

        return [
            'allowed' => true,
            'balance' => $credit->balance,
            'cost' => $costDetails['total_cost'],
            'parts' => $costDetails['parts'],
            'balance_after' => $credit->balance - $costDetails['total_cost'],
        ];
    }

    /**
     * Add credits (top up).
     */
    public function addCredit(int $amount, string $description, ?string $reference = null, ?array $metadata = null): SmsCreditTransaction
    {
        return DB::transaction(function () use ($amount, $description, $reference, $metadata) {
            $credit = SmsCredit::lockForUpdate()->find($this->credit->id);
            $balanceBefore = $credit->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update balance
            $credit->update([
                'balance' => $balanceAfter,
                'last_topped_up_at' => now(),
                'last_topped_up_by' => Auth::id(),
            ]);

            // Create transaction record
            $transaction = SmsCreditTransaction::create([
                'type' => SmsCreditTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference' => $reference,
                'performed_by' => Auth::id(),
                'metadata' => $metadata,
            ]);

            Log::info('SMS Credit added', [
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'performed_by' => Auth::id(),
            ]);

            return $transaction;
        });
    }

    /**
     * Deduct credit for SMS sent.
     */
    public function deductCredit(string $message, ?int $smsLogId = null, ?string $recipient = null): ?SmsCreditTransaction
    {
        return DB::transaction(function () use ($message, $smsLogId, $recipient) {
            $credit = SmsCredit::lockForUpdate()->find($this->credit->id);
            $costDetails = $credit->calculateMessageCost($message);
            $amount = $costDetails['total_cost'];

            $balanceBefore = $credit->balance;
            $balanceAfter = $balanceBefore - $amount;

            // Update balance
            $credit->update([
                'balance' => $balanceAfter,
            ]);

            // Create transaction record
            $transaction = SmsCreditTransaction::create([
                'type' => SmsCreditTransaction::TYPE_DEBIT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "SMS sent" . ($recipient ? " to {$recipient}" : ""),
                'sms_log_id' => $smsLogId,
                'performed_by' => Auth::id(),
                'metadata' => [
                    'message_length' => $costDetails['length'],
                    'message_parts' => $costDetails['parts'],
                    'credits_per_part' => $costDetails['credits_per_part'],
                ],
            ]);

            // Log if balance is low
            if ($credit->isBalanceLow()) {
                Log::warning('SMS Credit balance is low', [
                    'balance' => $balanceAfter,
                    'threshold' => $credit->low_balance_threshold,
                ]);
            }

            return $transaction;
        });
    }

    /**
     * Refund credits for failed SMS.
     */
    public function refundCredit(int $amount, ?int $smsLogId = null, string $reason = 'SMS delivery failed'): SmsCreditTransaction
    {
        return DB::transaction(function () use ($amount, $smsLogId, $reason) {
            $credit = SmsCredit::lockForUpdate()->find($this->credit->id);
            $balanceBefore = $credit->balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update balance
            $credit->update([
                'balance' => $balanceAfter,
            ]);

            // Create transaction record
            $transaction = SmsCreditTransaction::create([
                'type' => SmsCreditTransaction::TYPE_REFUND,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $reason,
                'sms_log_id' => $smsLogId,
                'performed_by' => Auth::id(),
            ]);

            Log::info('SMS Credit refunded', [
                'amount' => $amount,
                'reason' => $reason,
                'sms_log_id' => $smsLogId,
            ]);

            return $transaction;
        });
    }

    /**
     * Adjust credit balance (for corrections).
     */
    public function adjustBalance(int $newBalance, string $reason): SmsCreditTransaction
    {
        return DB::transaction(function () use ($newBalance, $reason) {
            $credit = SmsCredit::lockForUpdate()->find($this->credit->id);
            $balanceBefore = $credit->balance;
            $difference = $newBalance - $balanceBefore;

            // Update balance
            $credit->update([
                'balance' => $newBalance,
            ]);

            // Create transaction record
            $transaction = SmsCreditTransaction::create([
                'type' => SmsCreditTransaction::TYPE_ADJUSTMENT,
                'amount' => abs($difference),
                'balance_before' => $balanceBefore,
                'balance_after' => $newBalance,
                'description' => $reason,
                'performed_by' => Auth::id(),
                'metadata' => [
                    'adjustment_type' => $difference >= 0 ? 'increase' : 'decrease',
                ],
            ]);

            Log::info('SMS Credit adjusted', [
                'balance_before' => $balanceBefore,
                'balance_after' => $newBalance,
                'difference' => $difference,
                'reason' => $reason,
                'performed_by' => Auth::id(),
            ]);

            return $transaction;
        });
    }

    /**
     * Get statistics for dashboard.
     */
    public function getStatistics(): array
    {
        $credit = $this->credit->fresh();

        $todayDebits = SmsCreditTransaction::debits()->today()->sum('amount');
        $monthDebits = SmsCreditTransaction::debits()->thisMonth()->sum('amount');
        $monthCredits = SmsCreditTransaction::credits()->thisMonth()->sum('amount');

        $todaySmsCount = SmsCreditTransaction::debits()->today()->count();
        $monthSmsCount = SmsCreditTransaction::debits()->thisMonth()->count();

        return [
            'balance' => $credit->balance,
            'cost_per_sms' => $credit->cost_per_sms,
            'is_active' => $credit->is_active,
            'is_balance_low' => $credit->isBalanceLow(),
            'low_balance_threshold' => $credit->low_balance_threshold,
            'estimated_sms_remaining' => $credit->getEstimatedSmsCount(),
            'today' => [
                'credits_used' => $todayDebits,
                'sms_count' => $todaySmsCount,
            ],
            'this_month' => [
                'credits_used' => $monthDebits,
                'credits_added' => $monthCredits,
                'sms_count' => $monthSmsCount,
            ],
            'last_topped_up_at' => $credit->last_topped_up_at,
        ];
    }

    /**
     * Update SMS settings.
     */
    public function updateSettings(array $settings): SmsCredit
    {
        $allowedFields = ['cost_per_sms', 'low_balance_threshold', 'allow_negative_balance', 'is_active'];
        $filtered = array_intersect_key($settings, array_flip($allowedFields));

        $this->credit->update($filtered);

        Log::info('SMS Credit settings updated', [
            'settings' => $filtered,
            'updated_by' => Auth::id(),
        ]);

        return $this->credit->fresh();
    }
}
