<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GradeSubject extends Pivot
{
    use HasFactory;

    protected $table = 'grade_subject';

    protected $fillable = [
        'grade_id',
        'subject_id',
        'is_mandatory',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    // If you need timestamps in your pivot table
    public $timestamps = true;

    /**
     * Get the grade that this relationship belongs to.
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the subject that this relationship belongs to.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
