<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Grade;
use App\Models\FeeStructure;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Default cache TTL in seconds (1 hour)
     */
    const TTL = 3600;

    /**
     * Get the current active academic year (cached)
     */
    public function getCurrentAcademicYear(): ?AcademicYear
    {
        return Cache::remember('current_academic_year', self::TTL, function () {
            return AcademicYear::where('is_active', true)->first();
        });
    }

    /**
     * Get the current active term (cached)
     */
    public function getCurrentTerm(): ?Term
    {
        return Cache::remember('current_term', self::TTL, function () {
            return Term::where('is_active', true)->first();
        });
    }

    /**
     * Get all active grades (cached)
     */
    public function getActiveGrades()
    {
        return Cache::remember('active_grades', self::TTL, function () {
            return Grade::where('is_active', true)
                ->orderBy('level')
                ->get();
        });
    }

    /**
     * Get fee structures for a specific term (cached)
     */
    public function getFeeStructuresForTerm(int $termId)
    {
        return Cache::remember("fee_structures_term_{$termId}", self::TTL, function () use ($termId) {
            return FeeStructure::with(['grade', 'academicYear', 'term'])
                ->where('term_id', $termId)
                ->where('is_active', true)
                ->get();
        });
    }

    /**
     * Get fee structures for current term (cached)
     */
    public function getCurrentTermFeeStructures()
    {
        $currentTerm = $this->getCurrentTerm();

        if (!$currentTerm) {
            return collect([]);
        }

        return $this->getFeeStructuresForTerm($currentTerm->id);
    }

    /**
     * Clear all academic year related caches
     */
    public function clearAcademicYearCache(): void
    {
        Cache::forget('current_academic_year');
    }

    /**
     * Clear all term related caches
     */
    public function clearTermCache(): void
    {
        Cache::forget('current_term');
    }

    /**
     * Clear all grade related caches
     */
    public function clearGradeCache(): void
    {
        Cache::forget('active_grades');
    }

    /**
     * Clear fee structure cache for a specific term
     */
    public function clearFeeStructureCache(int $termId): void
    {
        Cache::forget("fee_structures_term_{$termId}");
    }

    /**
     * Clear all fee structure caches
     */
    public function clearAllFeeStructureCaches(): void
    {
        // Get all terms and clear their caches
        $terms = Term::all();
        foreach ($terms as $term) {
            $this->clearFeeStructureCache($term->id);
        }
    }

    /**
     * Clear all application caches
     */
    public function clearAllCaches(): void
    {
        $this->clearAcademicYearCache();
        $this->clearTermCache();
        $this->clearGradeCache();
        $this->clearAllFeeStructureCaches();

        // Clear any additional pattern-based caches
        Cache::flush();
    }

    /**
     * Warm up caches with frequently accessed data
     * Call this after clearing caches or on application boot
     */
    public function warmUpCaches(): void
    {
        $this->getCurrentAcademicYear();
        $this->getCurrentTerm();
        $this->getActiveGrades();

        $currentTerm = $this->getCurrentTerm();
        if ($currentTerm) {
            $this->getFeeStructuresForTerm($currentTerm->id);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $stats = [
            'current_academic_year' => Cache::has('current_academic_year'),
            'current_term' => Cache::has('current_term'),
            'active_grades' => Cache::has('active_grades'),
            'fee_structures_cached' => 0,
        ];

        // Check fee structure caches
        $terms = Term::all();
        foreach ($terms as $term) {
            if (Cache::has("fee_structures_term_{$term->id}")) {
                $stats['fee_structures_cached']++;
            }
        }

        return $stats;
    }
}
