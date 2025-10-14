<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'bus_fare_structure_id',
        'academic_year_id',
        'term_id',
        'month',
        'year',
        'amount',
        'amount_paid',
        'balance',
        'payment_status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'year' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function busFareStructure(): BelongsTo
    {
        return $this->belongsTo(BusFareStructure::class);
    }

    /**
     * Update payment status based on balance
     */
    public function updatePaymentStatus(): void
    {
        if ($this->balance <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'unpaid';
        }

        $this->save();
    }
}
