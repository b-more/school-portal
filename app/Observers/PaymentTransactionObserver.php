<?php

namespace App\Observers;

use App\Models\PaymentTransaction;
use App\Services\Accounting\AccountingIntegrationService;
use Illuminate\Support\Facades\Log;

class PaymentTransactionObserver
{
    public function __construct(
        protected AccountingIntegrationService $accountingService
    ) {}

    /**
     * Handle the PaymentTransaction "created" event.
     */
    public function created(PaymentTransaction $transaction): void
    {
        // Only process completed payment transactions
        if ($transaction->status !== 'completed') {
            return;
        }

        // Only process payment types (not adjustments or other types)
        if (!$transaction->isPayment()) {
            return;
        }

        try {
            $journalEntry = $this->accountingService->postFeePayment($transaction);

            if ($journalEntry) {
                Log::info('Fee payment posted to accounting', [
                    'transaction_id' => $transaction->id,
                    'journal_entry_id' => $journalEntry->id,
                    'amount' => $transaction->amount,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
            Log::error('Failed to post fee payment to accounting', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the PaymentTransaction "updated" event.
     */
    public function updated(PaymentTransaction $transaction): void
    {
        // If status changed to completed, post to accounting
        if ($transaction->wasChanged('status') &&
            $transaction->status === 'completed' &&
            $transaction->getOriginal('status') !== 'completed') {

            if (!$transaction->isPayment()) {
                return;
            }

            try {
                $journalEntry = $this->accountingService->postFeePayment($transaction);

                if ($journalEntry) {
                    Log::info('Fee payment posted to accounting on status update', [
                        'transaction_id' => $transaction->id,
                        'journal_entry_id' => $journalEntry->id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to post fee payment to accounting', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle the PaymentTransaction "deleted" event.
     * Note: We don't reverse accounting entries on delete - use proper voidinginstead.
     */
    public function deleted(PaymentTransaction $transaction): void
    {
        Log::warning('Payment transaction deleted - accounting entry may need manual reversal', [
            'transaction_id' => $transaction->id,
            'reference_number' => $transaction->reference_number,
            'amount' => $transaction->amount,
        ]);
    }
}
