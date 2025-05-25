<?php

namespace App\Services;

use App\Models\Term;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TermService
{
    /**
     * Get current active term based on today's date
     */
    public function getCurrentTerm(): ?Term
    {
        return Cache::remember('current_term', 3600, function () {
            $today = Carbon::now()->startOfDay();

            return Term::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->with(['academicYear'])
                ->first();
        });
    }

    /**
     * Get current active academic year
     */
    public function getCurrentAcademicYear(): ?AcademicYear
    {
        return Cache::remember('current_academic_year', 3600, function () {
            $today = Carbon::now()->startOfDay();

            return AcademicYear::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->first();
        });
    }

    /**
     * Get next term in sequence
     */
    public function getNextTerm(?Term $currentTerm = null): ?Term
    {
        $currentTerm = $currentTerm ?? $this->getCurrentTerm();

        if (!$currentTerm) {
            return null;
        }

        // Find next term in the same academic year
        $nextTerm = Term::where('academic_year_id', $currentTerm->academic_year_id)
            ->where('start_date', '>', $currentTerm->end_date)
            ->orderBy('start_date')
            ->first();

        if ($nextTerm) {
            return $nextTerm;
        }

        // Find first term of next academic year
        $nextAcademicYear = AcademicYear::where('start_date', '>', $currentTerm->academicYear->end_date)
            ->orderBy('start_date')
            ->first();

        if ($nextAcademicYear) {
            return Term::where('academic_year_id', $nextAcademicYear->id)
                ->orderBy('start_date')
                ->first();
        }

        return null;
    }

    /**
     * Get previous term in sequence
     */
    public function getPreviousTerm(?Term $currentTerm = null): ?Term
    {
        $currentTerm = $currentTerm ?? $this->getCurrentTerm();

        if (!$currentTerm) {
            return null;
        }

        // Find previous term in the same academic year
        $previousTerm = Term::where('academic_year_id', $currentTerm->academic_year_id)
            ->where('end_date', '<', $currentTerm->start_date)
            ->orderBy('end_date', 'desc')
            ->first();

        if ($previousTerm) {
            return $previousTerm;
        }

        // Find last term of previous academic year
        $previousAcademicYear = AcademicYear::where('end_date', '<', $currentTerm->academicYear->start_date)
            ->orderBy('end_date', 'desc')
            ->first();

        if ($previousAcademicYear) {
            return Term::where('academic_year_id', $previousAcademicYear->id)
                ->orderBy('end_date', 'desc')
                ->first();
        }

        return null;
    }

    /**
     * Validate if fee assignment is for correct term period
     */
    public function validateTermForFeeAssignment(Term $term): array
    {
        $today = Carbon::now();
        $warnings = [];
        $errors = [];

        // Check if term is in the future
        if ($term->start_date->gt($today)) {
            $warnings[] = "Term '{$term->name}' starts in the future ({$term->start_date->format('M j, Y')}). Early fee assignment.";
        }

        // Check if term has ended
        if ($term->end_date->lt($today)) {
            $warnings[] = "Term '{$term->name}' has ended ({$term->end_date->format('M j, Y')}). Late fee assignment.";
        }

        // Check if term dates are valid
        if ($term->start_date->gte($term->end_date)) {
            $errors[] = "Term '{$term->name}' has invalid dates. Start date must be before end date.";
        }

        return [
            'is_valid' => empty($errors),
            'warnings' => $warnings,
            'errors' => $errors,
            'is_current' => $term->start_date->lte($today) && $term->end_date->gte($today),
            'days_until_start' => $term->start_date->gt($today) ? $today->diffInDays($term->start_date) : 0,
            'days_since_end' => $term->end_date->lt($today) ? $term->end_date->diffInDays($today) : 0,
        ];
    }

    /**
     * Get terms for academic year with status
     */
    public function getTermsWithStatus(int $academicYearId): array
    {
        $terms = Term::where('academic_year_id', $academicYearId)
            ->orderBy('start_date')
            ->get();

        $today = Carbon::now();
        $result = [];

        foreach ($terms as $term) {
            $status = 'upcoming';
            if ($term->start_date->lte($today) && $term->end_date->gte($today)) {
                $status = 'current';
            } elseif ($term->end_date->lt($today)) {
                $status = 'completed';
            }

            $result[] = [
                'term' => $term,
                'status' => $status,
                'progress_percentage' => $this->calculateTermProgress($term),
                'validation' => $this->validateTermForFeeAssignment($term),
            ];
        }

        return $result;
    }

    /**
     * Calculate term progress percentage
     */
    private function calculateTermProgress(Term $term): float
    {
        $today = Carbon::now();

        if ($today->lt($term->start_date)) {
            return 0;
        }

        if ($today->gt($term->end_date)) {
            return 100;
        }

        $totalDays = $term->start_date->diffInDays($term->end_date);
        $elapsedDays = $term->start_date->diffInDays($today);

        return $totalDays > 0 ? round(($elapsedDays / $totalDays) * 100, 1) : 0;
    }

    /**
     * Get recommended term for new fee assignment
     */
    public function getRecommendedTermForFeeAssignment(): ?Term
    {
        $currentTerm = $this->getCurrentTerm();

        if ($currentTerm) {
            return $currentTerm;
        }

        // If no current term, get the next upcoming term
        $today = Carbon::now();

        return Term::where('start_date', '>', $today)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Clear term cache (call after term updates)
     */
    public function clearCache(): void
    {
        Cache::forget('current_term');
        Cache::forget('current_academic_year');
    }
}
