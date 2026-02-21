<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'account_id',
        'budget_amount',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'budget_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class, 'parent_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    public function getTotalExpenses($startDate = null, $endDate = null): float
    {
        $query = $this->expenses()->where('payment_status', '!=', 'cancelled');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        return $query->sum('total_amount');
    }

    public function getBudgetUsedPercentage($startDate = null, $endDate = null): float
    {
        if ($this->budget_amount <= 0) {
            return 0;
        }

        $totalExpenses = $this->getTotalExpenses($startDate, $endDate);
        return round(($totalExpenses / $this->budget_amount) * 100, 2);
    }
}
