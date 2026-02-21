<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_days',
        'is_paid',
        'requires_documentation',
        'is_active',
        'gender_specific',
        'max_consecutive_days',
        'min_service_days',
        'carry_forward',
        'max_carry_forward_days',
    ];

    protected $casts = [
        'default_days' => 'integer',
        'is_paid' => 'boolean',
        'requires_documentation' => 'boolean',
        'is_active' => 'boolean',
        'max_consecutive_days' => 'integer',
        'min_service_days' => 'integer',
        'carry_forward' => 'boolean',
        'max_carry_forward_days' => 'integer',
    ];

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Check if leave type is available for an employee based on gender
     */
    public function isAvailableFor(Employee $employee): bool
    {
        if ($this->gender_specific === 'all') {
            return true;
        }

        return $this->gender_specific === $employee->gender;
    }

    /**
     * Check if employee has served minimum days to be eligible
     */
    public function isEligible(Employee $employee): bool
    {
        if (!$employee->joining_date) {
            return false;
        }

        $serviceDays = $employee->joining_date->diffInDays(now());
        return $serviceDays >= $this->min_service_days;
    }
}
