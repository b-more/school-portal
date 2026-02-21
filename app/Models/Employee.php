<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        // Personal Information
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'city',
        'province',
        'profile_photo',

        // Statutory/Compliance (Zambia)
        'nrc_number',
        'napsa_number',
        'tpin_number',
        'nhima_number',

        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',

        // Next of Kin
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship',
        'next_of_kin_address',

        // Employment Details
        'employee_id',
        'employee_number',
        'role_id',
        'department',
        'position',
        'employment_type',
        'joining_date',
        'contract_start_date',
        'contract_end_date',
        'probation_end_date',
        'confirmation_date',
        'termination_date',
        'termination_reason',
        'status',
        'basic_salary',
        'salary_grade_id',
        'designation_changed_date',

        // Bank Details
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'bank_account_name',

        // Qualifications
        'highest_qualification',
        'qualification_institution',
        'qualification_year',
        'professional_certifications',

        // Leave Allocation
        'annual_leave_days',
        'sick_leave_days',

        // Relationships
        'user_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'termination_date' => 'date',
        'designation_changed_date' => 'date',
        'basic_salary' => 'decimal:2',
        'annual_leave_days' => 'integer',
        'sick_leave_days' => 'integer',
        'qualification_year' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function salaryGrade(): BelongsTo
    {
        return $this->belongsTo(SalaryGrade::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function homeworks(): HasMany
    {
        return $this->hasMany(Homework::class, 'assigned_by');
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'recorded_by');
    }

    public function school_classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'class_id')
                    ->select(['school_classes.*']) // Make sure this matches your actual table name
                    ->withPivot('role', 'is_primary')
                    ->withTimestamps();
    }

    public function classes(): BelongsToMany
    {
        return $this->school_classes();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_subject')
                    ->withTimestamps()
                    ->select(['subjects.*']); // This fixes the ambiguous 'id' column issue
    }

    public function classSubjectAssignments(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject_teacher', 'employee_id', 'class_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function headOfSections(): HasMany
    {
        return $this->hasMany(SchoolSection::class, 'head_of_section_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function isTeacher(): bool
    {
        return $this->role_id === \App\Constants\RoleConstants::TEACHER;
    }

    public function isAdmin(): bool
    {
        return $this->role_id === \App\Constants\RoleConstants::ADMIN;
    }

    /**
     * This is for compatibility with Filament - DO NOT USE for regular relationships
     */
    public function classSections()
    {
        return $this->belongsToMany(ClassSection::class, 'teacher_class_section', 'teacher_id', 'class_section_id')
                    ->using(TeacherClassSection::class)
                    ->withTimestamps();
    }

    /**
     * Relation to class sections through teacher - this is the proper relationship
     */
    public function schoolClasses(): HasManyThrough
    {
        return $this->hasManyThrough(
            ClassSection::class,
            Teacher::class,
            'employee_id',
            'class_teacher_id',
            'id',
            'id'
        );
    }

    // Leave Management Relationships
    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    // Helper Methods
    /**
     * Get years of service
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->joining_date) {
            return 0;
        }
        return round($this->joining_date->diffInYears(now()), 1);
    }

    /**
     * Get age
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    /**
     * Check if employee is on probation
     */
    public function isOnProbation(): bool
    {
        if (!$this->probation_end_date) {
            return false;
        }
        return $this->probation_end_date->isFuture() && !$this->confirmation_date;
    }

    /**
     * Check if contract is expiring soon (within 30 days)
     */
    public function isContractExpiringSoon(int $days = 30): bool
    {
        if (!$this->contract_end_date) {
            return false;
        }
        return $this->contract_end_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Get leave balance for a specific leave type
     */
    public function getLeaveBalance(int $leaveTypeId, int $year = null): LeaveBalance
    {
        return LeaveBalance::getOrCreate($this->id, $leaveTypeId, $year ?? date('Y'));
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Get present appointment date (designation changed date or joining date)
     */
    public function getPresentAppointmentDateAttribute(): ?\Carbon\Carbon
    {
        return $this->designation_changed_date ?? $this->joining_date;
    }

    /**
     * Get next appraisal date (one year after present appointment)
     */
    public function getNextAppraisalDateAttribute(): ?\Carbon\Carbon
    {
        $presentDate = $this->present_appointment_date;
        if (!$presentDate) {
            return null;
        }

        // Get the next appraisal date (same day/month but next year)
        $nextAppraisal = $presentDate->copy()->addYear();

        // If next appraisal has already passed, move to the following year
        while ($nextAppraisal->isPast()) {
            $nextAppraisal->addYear();
        }

        return $nextAppraisal;
    }

    /**
     * Get total leave accrued for current year
     */
    public function getTotalLeaveAccruedAttribute(): float
    {
        $currentYear = date('Y');
        return $this->leaveBalances()
            ->whereHas('leaveType', fn($q) => $q->where('is_active', true))
            ->where('year', $currentYear)
            ->sum('allocated_days');
    }

    /**
     * Get total leave taken for current year
     */
    public function getTotalLeaveTakenAttribute(): float
    {
        $currentYear = date('Y');
        return $this->leaveBalances()
            ->whereHas('leaveType', fn($q) => $q->where('is_active', true))
            ->where('year', $currentYear)
            ->sum('used_days');
    }
}
