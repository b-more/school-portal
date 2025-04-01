<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade',
        'term',
        'academic_year',
        'basic_fee',
        'additional_charges',
        'total_fee',
        'description',
        'is_active',
    ];

    protected $casts = [
        'additional_charges' => 'json',
        'basic_fee' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function studentFees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }
}
