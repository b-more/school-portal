<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'term_id',
        'academic_year_id',
        'basic_fee',
        'additional_charges',
        'total_fee',
        'description',
        'is_active',
        'name',
    ];

    protected $casts = [
        'additional_charges' => 'array', // Using 'array' instead of 'json' for better handling
        'basic_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Accessor to ensure additional_charges is always an array
     */
    public function getAdditionalChargesAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Mutator to ensure additional_charges is always stored properly
     */
    public function setAdditionalChargesAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['additional_charges'] = json_encode($value);
        } else if (is_string($value)) {
            // If it's already a JSON string, validate it first
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->attributes['additional_charges'] = $value;
            } else {
                $this->attributes['additional_charges'] = json_encode([]);
            }
        } else {
            $this->attributes['additional_charges'] = json_encode([]);
        }
    }

    /**
     * Calculate the total fee based on basic fee and additional charges
     */
    public function calculateTotalFee()
    {
        $total = is_numeric($this->basic_fee) ? (float) $this->basic_fee : 0;

        $additionalCharges = $this->additional_charges;
        if (is_array($additionalCharges)) {
            foreach ($additionalCharges as $charge) {
                if (isset($charge['amount']) && is_numeric($charge['amount'])) {
                    $total += (float) $charge['amount'];
                }
            }
        }

        return round($total, 2);
    }

    /**
     * Auto-calculate total fee before saving
     */
    protected static function booted()
    {
        static::saving(function ($feeStructure) {
            if (!isset($feeStructure->total_fee) || $feeStructure->isDirty(['basic_fee', 'additional_charges'])) {
                $feeStructure->total_fee = $feeStructure->calculateTotalFee();
            }
        });
    }

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    // Helper method to get grade name (for backward compatibility)
    public function getGradeNameAttribute()
    {
        return $this->grade?->name ?? 'Unknown Grade';
    }
}
