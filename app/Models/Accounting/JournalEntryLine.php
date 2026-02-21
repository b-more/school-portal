<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit_amount',
        'credit_amount',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function isDebit(): bool
    {
        return $this->debit_amount > 0;
    }

    public function isCredit(): bool
    {
        return $this->credit_amount > 0;
    }

    public function getAmount(): float
    {
        return $this->isDebit() ? $this->debit_amount : $this->credit_amount;
    }
}
