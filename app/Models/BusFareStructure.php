<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusFareStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name',
        'payment_plan',
        'monthly_amount',
        'term_amount',
        'academic_year_id',
        'term_id',
        'is_active',
        'description',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'term_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function busPayments(): HasMany
    {
        return $this->hasMany(BusPayment::class);
    }

    /**
     * Get amount based on payment plan
     */
    public function getAmount(): float
    {
        return $this->payment_plan === 'monthly'
            ? (float) $this->monthly_amount
            : (float) $this->term_amount;
    }
}
