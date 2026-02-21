<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'account_category_id',
        'parent_id',
        'code',
        'name',
        'description',
        'account_type',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_system',
        'allow_direct_posting',
        'level',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'allow_direct_posting' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    public function bankAccount(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePostable($query)
    {
        return $query->where('allow_direct_posting', true);
    }

    public function scopeOfCategory($query, int $categoryId)
    {
        return $query->where('account_category_id', $categoryId);
    }

    public function scopeAssets($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'asset'));
    }

    public function scopeLiabilities($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'liability'));
    }

    public function scopeEquity($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'equity'));
    }

    public function scopeRevenue($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'revenue'));
    }

    public function scopeExpenses($query)
    {
        return $query->whereHas('category', fn($q) => $q->where('type', 'expense'));
    }

    public function getFullNameAttribute(): string
    {
        return $this->code . ' - ' . $this->name;
    }

    public function updateBalance(float $debit, float $credit): void
    {
        if ($this->account_type === 'debit') {
            $this->current_balance += ($debit - $credit);
        } else {
            $this->current_balance += ($credit - $debit);
        }
        $this->save();
    }

    public function getBalanceAsOf($date): float
    {
        $debits = $this->journalLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')->whereDate('entry_date', '<=', $date))
            ->sum('debit_amount');

        $credits = $this->journalLines()
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')->whereDate('entry_date', '<=', $date))
            ->sum('credit_amount');

        if ($this->account_type === 'debit') {
            return $this->opening_balance + $debits - $credits;
        }
        return $this->opening_balance + $credits - $debits;
    }
}
