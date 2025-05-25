<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_section_id',
        'name',
        'code',
        'level',
        'description',
        'capacity',
        'breakeven_number',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the school section that this grade belongs to
     */
    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }

    /**
     * Get homework for this grade
     */
    public function homework(): HasMany
    {
        return $this->hasMany(Homework::class);
    }

    /**
     * Get class sections for this grade
     */
    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    /**
     * Get active class sections for current academic year
     */
    public function activeClassSections(): HasMany
    {
        $currentAcademicYear = AcademicYear::current();

        return $this->hasMany(ClassSection::class)
            ->where('is_active', true)
            ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                return $query->where('academic_year_id', $currentAcademicYear->id);
            });
    }

    /**
     * Get subjects associated with this grade
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'grade_subject')
                    ->withTimestamps();
    }

    /**
     * Get students directly assigned to this grade
     */
    public function studentsDirectly(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get students directly assigned to this grade
     * Since your students have grade_id but class_section_id is NULL
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get students for a specific academic year
     * Since students are directly linked to grades, we filter by enrollment status and dates
     */
    public function studentsForAcademicYear($academicYearId = null)
    {
        // For now, return all active students since they're directly linked to grades
        return $this->hasMany(Student::class)
            ->where('enrollment_status', 'active');
    }

    /**
     * Get fee structures for this grade.
     */
    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    /**
     * Calculate total students in this grade
     * Since students are directly linked to grades, this is straightforward
     */
    public function getTotalStudentsAttribute()
    {
        return $this->students()->where('enrollment_status', 'active')->count();
    }

    /**
     * Get total students for a specific academic year
     */
    public function getTotalStudentsForYear($academicYearId = null)
    {
        return $this->studentsForAcademicYear($academicYearId)->count();
    }

    /**
     * Get students breakdown by class sections for current academic year
     * Since students are not assigned to class sections, we'll show the total for the grade
     * and suggest which sections they could be distributed to
     */
    public function getStudentsBreakdownAttribute()
    {
        $totalStudents = $this->getTotalStudentsAttribute();
        $sections = $this->activeClassSections()->get();

        if ($totalStudents === 0) {
            return [
                'sections' => [],
                'total' => 0,
                'grade_name' => $this->name,
                'note' => 'No students enrolled in this grade'
            ];
        }

        if ($sections->isEmpty()) {
            return [
                'sections' => [],
                'total' => $totalStudents,
                'grade_name' => $this->name,
                'note' => 'Students are assigned to grade but no class sections exist'
            ];
        }

        // Since students aren't assigned to specific sections,
        // we'll show them as "unassigned to sections"
        return [
            'sections' => [
                [
                    'section_name' => 'Unassigned',
                    'section_code' => 'N/A',
                    'student_count' => $totalStudents,
                ]
            ],
            'total' => $totalStudents,
            'grade_name' => $this->name,
            'note' => 'Students need to be assigned to specific class sections',
            'available_sections' => $sections->pluck('name')->toArray()
        ];
    }

    /**
     * Get formatted student count display
     */
    public function getFormattedStudentCountAttribute()
    {
        $breakdown = $this->getStudentsBreakdownAttribute();

        if (empty($breakdown['sections'])) {
            return "0 students";
        }

        if (count($breakdown['sections']) === 1) {
            $section = $breakdown['sections'][0];
            return "{$breakdown['total']} students ({$this->name} {$section['section_name']})";
        }

        $sectionDetails = collect($breakdown['sections'])
            ->map(fn($section) => "{$section['section_name']}: {$section['student_count']}")
            ->join(', ');

        return "{$breakdown['total']} students ({$sectionDetails})";
    }

    /**
     * Check if grade is at capacity
     */
    public function isAtCapacity()
    {
        return $this->getTotalStudentsAttribute() >= $this->capacity;
    }

    /**
     * Check if grade needs a new section
     */
    public function needsNewSection()
    {
        $activeSections = $this->activeClassSections();

        if ($activeSections->count() === 0) {
            return true;
        }

        $allFull = true;
        foreach ($activeSections->get() as $section) {
            if (!$section->isAtCapacity()) {
                $allFull = false;
                break;
            }
        }

        return $allFull || $this->getTotalStudentsAttribute() >= $this->breakeven_number;
    }

    /**
     * Scope to get grades with student counts
     * Since students are directly linked to grades, this is simpler
     */
    public function scopeWithCurrentStudentCounts($query)
    {
        return $query->withCount([
            'students as students_count' => function ($subQuery) {
                $subQuery->where('enrollment_status', 'active');
            }
        ]);
    }
}
