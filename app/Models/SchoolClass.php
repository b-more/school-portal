<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'department',
        'grade',
        'section',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // public function teachers(): BelongsToMany
    // {
    //     return $this->belongsToMany(Employee::class, 'class_teacher', 'class_id', 'employee_id')
    //                 ->withPivot('role', 'is_primary')
    //                 ->withTimestamps();
    // }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'class_id', 'subject_id')
                    ->withPivot('employee_id')
                    ->withTimestamps();
    }

    public function subjectTeachers(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'class_subject_teacher', 'class_id', 'employee_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'class_teacher', 'class_id', 'employee_id')
                    ->select(['employees.*']) // This fixes the ambiguous 'id' column issue
                    ->withPivot('role', 'is_primary')
                    ->withTimestamps();
    }

    /**
     * Alias for employees() to get class teachers
     */
    public function teachers(): BelongsToMany
    {
        return $this->employees();
    }
}
