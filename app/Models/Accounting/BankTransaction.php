<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'type',
        'amount',
        'reference',
        'description',
        'payee',
        'reconciled',
        'reconciled_at',
        'reconciled_by',
        'journal_entry_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_FEE = 'fee';
    public const TYPE_CHARGE = 'charge';
    public const TYPE_INTEREST = 'interest';

    public const TYPES = [
        self::TYPE_DEPOSIT => 'Deposit',
        self::TYPE_WITHDRAWAL => 'Withdrawal',
        self::TYPE_TRANSFER => 'Transfer',
        self::TYPE_FEE => 'Bank Fee',
        self::TYPE_CHARGE => 'Bank Charge',
        self::TYPE_INTEREST => 'Interest',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function scopeReconciled($query)
    {
        return $query->where('reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('reconciled', false);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeDeposits($query)
    {
        return $query->whereIn('type', [self::TYPE_DEPOSIT, self::TYPE_INTEREST]);
    }

    public function scopeWithdrawals($query)
    {
        return $query->whereIn('type', [self::TYPE_WITHDRAWAL, self::TYPE_TRANSFER, self::TYPE_FEE, self::TYPE_CHARGE]);
    }

    public function isDeposit(): bool
    {
        return in_array($this->type, [self::TYPE_DEPOSIT, self::TYPE_INTEREST]);
    }

    public function isWithdrawal(): bool
    {
        return in_array($this->type, [self::TYPE_WITHDRAWAL, self::TYPE_TRANSFER, self::TYPE_FEE, self::TYPE_CHARGE]);
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->isDeposit() ? $this->amount : -$this->amount;
    }

    public function reconcile(int $userId): void
    {
        $this->reconciled = true;
        $this->reconciled_at = now();
        $this->reconciled_by = $userId;
        $this->save();
    }

    public function unreconcile(): void
    {
        $this->reconciled = false;
        $this->reconciled_at = null;
        $this->reconciled_by = null;
        $this->save();
    }
}
