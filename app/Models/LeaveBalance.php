<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'carried_forward',
        'adjustment_days',
        'adjustment_reason',
    ];

    protected $casts = [
        'year' => 'integer',
        'allocated_days' => 'integer',
        'used_days' => 'integer',
        'carried_forward' => 'integer',
        'adjustment_days' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the remaining leave balance
     */
    public function getRemainingDaysAttribute(): int
    {
        return $this->allocated_days + $this->carried_forward + $this->adjustment_days - $this->used_days;
    }

    /**
     * Get total available days
     */
    public function getTotalDaysAttribute(): int
    {
        return $this->allocated_days + $this->carried_forward + $this->adjustment_days;
    }

    /**
     * Check if employee has sufficient balance for requested days
     */
    public function hasSufficientBalance(int $days): bool
    {
        return $this->remaining_days >= $days;
    }

    /**
     * Deduct days from balance
     */
    public function deductDays(int $days): bool
    {
        if (!$this->hasSufficientBalance($days)) {
            return false;
        }

        $this->used_days += $days;
        return $this->save();
    }

    /**
     * Restore days to balance (e.g., when leave is cancelled)
     */
    public function restoreDays(int $days): bool
    {
        $this->used_days = max(0, $this->used_days - $days);
        return $this->save();
    }

    /**
     * Get or create leave balance for an employee
     */
    public static function getOrCreate(int $employeeId, int $leaveTypeId, int $year = null): self
    {
        $year = $year ?? date('Y');
        $leaveType = LeaveType::find($leaveTypeId);

        return static::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'allocated_days' => $leaveType?->default_days ?? 0,
                'used_days' => 0,
                'carried_forward' => 0,
                'adjustment_days' => 0,
            ]
        );
    }
}
