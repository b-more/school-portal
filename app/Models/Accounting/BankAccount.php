<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'account_id',
        'bank_name',
        'account_name',
        'account_number',
        'branch',
        'swift_code',
        'currency',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function chartAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function incomeRecords(): HasMany
    {
        return $this->hasMany(IncomeRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->bank_name . ' - ' . $this->account_number;
    }

    public function getMaskedAccountNumberAttribute(): string
    {
        $length = strlen($this->account_number);
        if ($length <= 4) {
            return $this->account_number;
        }
        return str_repeat('*', $length - 4) . substr($this->account_number, -4);
    }

    public function deposit(float $amount, string $description = null, string $reference = null): BankTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_date' => now(),
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);

        $this->current_balance += $amount;
        $this->save();

        return $transaction;
    }

    public function withdraw(float $amount, string $description = null, string $reference = null): BankTransaction
    {
        $transaction = $this->transactions()->create([
            'transaction_date' => now(),
            'type' => 'withdrawal',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);

        $this->current_balance -= $amount;
        $this->save();

        return $transaction;
    }

    public function recalculateBalance(): void
    {
        $deposits = $this->transactions()
            ->whereIn('type', ['deposit', 'interest'])
            ->sum('amount');

        $withdrawals = $this->transactions()
            ->whereIn('type', ['withdrawal', 'transfer', 'fee', 'charge'])
            ->sum('amount');

        $this->current_balance = $this->opening_balance + $deposits - $withdrawals;
        $this->save();
    }
}
