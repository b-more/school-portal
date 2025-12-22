<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'normal_balance',
        'is_system',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const TYPE_ASSET = 'asset';
    public const TYPE_LIABILITY = 'liability';
    public const TYPE_EQUITY = 'equity';
    public const TYPE_REVENUE = 'revenue';
    public const TYPE_EXPENSE = 'expense';

    public const TYPES = [
        self::TYPE_ASSET => 'Asset',
        self::TYPE_LIABILITY => 'Liability',
        self::TYPE_EQUITY => 'Equity',
        self::TYPE_REVENUE => 'Revenue',
        self::TYPE_EXPENSE => 'Expense',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'account_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isDebitNormal(): bool
    {
        return $this->normal_balance === 'debit';
    }

    public function isCreditNormal(): bool
    {
        return $this->normal_balance === 'credit';
    }
}
