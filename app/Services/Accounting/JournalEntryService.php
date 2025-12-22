<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Exception;

class JournalEntryService
{
    /**
     * Create a new journal entry with lines
     */
    public function createEntry(array $data, array $lines): JournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            // Validate that debits equal credits
            $totalDebit = collect($lines)->sum('debit_amount');
            $totalCredit = collect($lines)->sum('credit_amount');

            if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
                throw new Exception('Journal entry must balance. Debits: ' . $totalDebit . ', Credits: ' . $totalCredit);
            }

            // Create the journal entry
            $entry = JournalEntry::create([
                'entry_date' => $data['entry_date'] ?? now(),
                'description' => $data['description'],
                'notes' => $data['notes'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => $data['status'] ?? JournalEntry::STATUS_DRAFT,
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Create journal entry lines
            foreach ($lines as $index => $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit_amount'] ?? 0,
                    'credit_amount' => $line['credit_amount'] ?? 0,
                    'description' => $line['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }

            return $entry->fresh(['lines.account']);
        });
    }

    /**
     * Create and immediately post a journal entry
     */
    public function createAndPostEntry(array $data, array $lines): JournalEntry
    {
        $entry = $this->createEntry($data, $lines);
        $this->postEntry($entry->id, $data['created_by'] ?? auth()->id());
        return $entry->fresh();
    }

    /**
     * Post a draft journal entry
     */
    public function postEntry(int $entryId, int $userId): bool
    {
        $entry = JournalEntry::with('lines.account')->findOrFail($entryId);

        if (!$entry->isDraft()) {
            throw new Exception('Only draft entries can be posted.');
        }

        if (!$entry->isBalanced()) {
            throw new Exception('Journal entry must balance before posting.');
        }

        return DB::transaction(function () use ($entry, $userId) {
            return $entry->post($userId);
        });
    }

    /**
     * Void a posted journal entry
     */
    public function voidEntry(int $entryId, int $userId, string $reason): bool
    {
        $entry = JournalEntry::with('lines.account')->findOrFail($entryId);

        if (!$entry->isPosted()) {
            throw new Exception('Only posted entries can be voided.');
        }

        return DB::transaction(function () use ($entry, $userId, $reason) {
            return $entry->void($userId, $reason);
        });
    }

    /**
     * Get account balance as of a specific date
     */
    public function getAccountBalance(int $accountId, $asOfDate = null): float
    {
        $account = ChartOfAccount::findOrFail($accountId);
        return $account->getBalanceAsOf($asOfDate ?? now());
    }

    /**
     * Get account transactions for a date range
     */
    public function getAccountTransactions(int $accountId, $startDate, $endDate): array
    {
        $lines = JournalEntryLine::with(['journalEntry'])
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate) {
                $query->where('status', JournalEntry::STATUS_POSTED)
                    ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->orderBy('created_at')
            ->get();

        $account = ChartOfAccount::find($accountId);
        $openingBalance = $account->getBalanceAsOf($startDate->subDay());

        $transactions = [];
        $runningBalance = $openingBalance;

        foreach ($lines as $line) {
            if ($account->account_type === 'debit') {
                $runningBalance += ($line->debit_amount - $line->credit_amount);
            } else {
                $runningBalance += ($line->credit_amount - $line->debit_amount);
            }

            $transactions[] = [
                'date' => $line->journalEntry->entry_date,
                'entry_number' => $line->journalEntry->entry_number,
                'description' => $line->description ?? $line->journalEntry->description,
                'debit' => $line->debit_amount,
                'credit' => $line->credit_amount,
                'balance' => $runningBalance,
            ];
        }

        return [
            'account' => $account,
            'opening_balance' => $openingBalance,
            'transactions' => $transactions,
            'closing_balance' => $runningBalance,
        ];
    }

    /**
     * Create a reversing entry for a posted journal
     */
    public function createReversingEntry(int $originalEntryId, int $userId): JournalEntry
    {
        $original = JournalEntry::with('lines')->findOrFail($originalEntryId);

        if (!$original->isPosted()) {
            throw new Exception('Only posted entries can be reversed.');
        }

        $lines = [];
        foreach ($original->lines as $line) {
            $lines[] = [
                'account_id' => $line->account_id,
                'debit_amount' => $line->credit_amount, // Swap
                'credit_amount' => $line->debit_amount, // Swap
                'description' => 'Reversal: ' . ($line->description ?? ''),
            ];
        }

        return $this->createAndPostEntry([
            'entry_date' => now(),
            'description' => 'Reversal of ' . $original->entry_number,
            'reference_type' => JournalEntry::class,
            'reference_id' => $original->id,
            'academic_year_id' => $original->academic_year_id,
            'created_by' => $userId,
        ], $lines);
    }
}
