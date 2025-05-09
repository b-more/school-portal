<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// class StudentFee extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'student_id',
//         'fee_structure_id',
//         'academic_year_id',
//         'term_id',
//         'grade_id', // Changed from 'grade' to 'grade_id'
//         'amount_paid',
//         'balance',
//         'payment_status',
//         'payment_date',
//         'receipt_number',
//         'payment_method',
//         'notes',
//         'send_sms_notification',
//     ];

//     protected $casts = [
//         'amount_paid' => 'decimal:2',
//         'balance' => 'decimal:2',
//         'payment_date' => 'date',
//         'send_sms_notification' => 'boolean',
//     ];

//     /**
//      * Get the student that owns the fee.
//      */
//     public function student(): BelongsTo
//     {
//         return $this->belongsTo(Student::class);
//     }

//     /**
//      * Get the fee structure associated with the fee.
//      */
//     public function feeStructure(): BelongsTo
//     {
//         return $this->belongsTo(FeeStructure::class);
//     }

//     /**
//      * Get the academic year associated with the fee.
//      */
//     public function academicYear(): BelongsTo
//     {
//         return $this->belongsTo(AcademicYear::class);
//     }

//     /**
//      * Get the term associated with the fee.
//      */
//     public function term(): BelongsTo
//     {
//         return $this->belongsTo(Term::class);
//     }

//     /**
//      * Get the grade associated with the fee.
//      */
//     public function grade(): BelongsTo
//     {
//         return $this->belongsTo(Grade::class);
//     }

//     /**
//      * Helper method to get grade name (for backward compatibility)
//      */
//     public function getGradeNameAttribute()
//     {
//         return $this->grade?->name ?? $this->attributes['grade'] ?? 'Unknown Grade';
//     }
// }


class StudentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'academic_year_id',
        'term_id',
        'grade_id',
        'amount_paid',
        'balance',
        'payment_status',
        'payment_date',
        'receipt_number',
        'payment_method',
        'notes',
        'send_sms_notification',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'payment_date' => 'date',
        'send_sms_notification' => 'boolean',
    ];

    /**
     * Get the student that owns the fee.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the fee structure associated with the fee.
     */
    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Get the academic year associated with the fee.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the term associated with the fee.
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Get the grade associated with the fee.
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
