<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_requested',
        'is_half_day',
        'half_day_period',
        'reason',
        'contact_during_leave',
        'handover_notes',
        'covering_employee_id',
        'attachment',
        'status',
        'hod_approved_by',
        'hod_approved_at',
        'hod_remarks',
        'head_approved_by',
        'head_approved_at',
        'head_remarks',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'actual_return_date',
        'return_remarks',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_requested' => 'integer',
        'is_half_day' => 'boolean',
        'hod_approved_at' => 'datetime',
        'head_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'actual_return_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = static::generateReferenceNumber();
            }
        });
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = 'LV';
        $year = date('Y');
        $latest = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $latest ? ((int) substr($latest->reference_number, -5)) + 1 : 1;

        return $prefix . $year . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function hodApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function headApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_approved_by');
    }

    public function finalApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Alias for finalApprover for PDF template
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function coveringEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'covering_employee_id');
    }

    /**
     * Check if application can be approved by HOD
     */
    public function canBeApprovedByHod(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application can be approved by Headteacher
     */
    public function canBeApprovedByHead(): bool
    {
        return $this->status === 'approved_by_hod';
    }

    /**
     * Check if application can be finally approved
     */
    public function canBeFinallyApproved(): bool
    {
        return $this->status === 'approved_by_head';
    }

    /**
     * Check if application is still pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'approved_by_hod', 'approved_by_head']);
    }

    /**
     * Check if application is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved_by_hod' => 'info',
            'approved_by_head' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'approved_by_hod' => 'HOD Approved',
            'approved_by_head' => 'Head Approved',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
