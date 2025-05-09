<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role_id',
        'role',
        'department',
        'position',
        'joining_date',
        'status',
        'basic_salary',
        'employee_id',
        'profile_photo',
        'user_id',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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

    // public function school_classes(): BelongsToMany
    // {
    //     return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'employee_id', 'class_id')
    //                 ->withPivot('role', 'is_primary')
    //                 ->withTimestamps();
    // }

    public function school_classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'employee_id', 'class_id')
                    ->select(['classes.*']) // This fixes the ambiguous 'id' column issue
                    ->withPivot('role', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Alias for school_classes() relationship
     * This resolves the "Call to undefined method App\Models\Employee::classes()" error
     */
    public function classes(): BelongsToMany
    {
        return $this->school_classes();
    }

    /**
     * Alias for school_classes() relationship
     * This resolves the "Call to undefined method App\Models\Employee::classes()" error
     */
    // public function classes(): BelongsToMany
    // {
    //     return $this->school_classes();
    // }

    // public function subjects(): BelongsToMany
    // {
    //     return $this->belongsToMany(Subject::class, 'employee_subject')
    //                 ->withTimestamps();
    // }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'employee_subject')
                    ->withTimestamps()
                    ->select(['subjects.*']); // This fixes the ambiguous 'id' column issue
    }



    /**
     * Get the subject-class assignments for this employee (teacher)
     */
    public function classSubjectAssignments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject_teacher', 'employee_id', 'class_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }
}
