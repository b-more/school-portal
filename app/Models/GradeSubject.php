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

    /**
     * Get all teachers teaching this subject in this grade
     * by looking at subject_teachings table
     */
    public function getTeachersAttribute()
    {
        if (!$this->grade_id || !$this->subject_id) {
            return collect();
        }

        // Get all class sections for this grade
        $classSectionIds = \App\Models\ClassSection::where('grade_id', $this->grade_id)
            ->pluck('id');

        // Get all teachers teaching this subject in these class sections
        $teachers = \DB::table('subject_teachings')
            ->join('teachers', 'subject_teachings.teacher_id', '=', 'teachers.id')
            ->join('class_sections', 'subject_teachings.class_section_id', '=', 'class_sections.id')
            ->where('subject_teachings.subject_id', $this->subject_id)
            ->whereIn('subject_teachings.class_section_id', $classSectionIds)
            ->select(
                'teachers.id',
                'teachers.name',
                'class_sections.name as class_section_name'
            )
            ->distinct()
            ->get();

        return $teachers;
    }
}
