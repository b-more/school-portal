<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'tax_pin',
        'payment_terms',
        'account_id',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (empty($vendor->code)) {
                $vendor->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastVendor = static::orderBy('id', 'desc')->first();
        $sequence = $lastVendor ? $lastVendor->id + 1 : 1;
        return 'VEN' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'payee_id')
            ->where('payee_type', 'vendor');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalPurchases(): float
    {
        return $this->expenses()->sum('total_amount');
    }

    public function getOutstandingBalance(): float
    {
        return $this->expenses()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(total_amount - amount_paid) as balance')
            ->value('balance') ?? 0;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([$this->address, $this->city]);
        return implode(', ', $parts);
    }
}
