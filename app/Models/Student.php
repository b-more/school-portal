<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date_of_birth',
        'place_of_birth',
        'religious_denomination',
        'standard_of_education',
        'smallpox_vaccination',
        'date_vaccinated',
        'gender',
        'address',
        'student_id_number',
        'parent_guardian_id',
        'grade',
        'admission_date',
        'enrollment_status',
        'user_id',
        'previous_school',
        'profile_photo',
        'medical_information',
        'notes',
        'role'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function parentGuardian(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(StudentFee::class);
    }

    public function homeworkSubmissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
