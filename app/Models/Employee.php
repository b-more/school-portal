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
        'name',
        'email',
        'phone',
        'role_id',
        'employee_number',
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

    public function school_classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'employee_id', 'class_id')
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
}
