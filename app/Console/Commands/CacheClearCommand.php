<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'school:cache-clear
                            {--type=all : Type of cache to clear (all, academic-year, term, grade, fee-structure)}
                            {--warm : Warm up the cache after clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear school application caches (academic years, terms, grades, fee structures)';

    /**
     * The cache service instance
     */
    protected CacheService $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $warm = $this->option('warm');

        $this->info('Clearing school application caches...');

        switch ($type) {
            case 'academic-year':
                $this->cacheService->clearAcademicYearCache();
                $this->info('✓ Academic year cache cleared');
                break;

            case 'term':
                $this->cacheService->clearTermCache();
                $this->info('✓ Term cache cleared');
                break;

            case 'grade':
                $this->cacheService->clearGradeCache();
                $this->info('✓ Grade cache cleared');
                break;

            case 'fee-structure':
                $this->cacheService->clearAllFeeStructureCaches();
                $this->info('✓ Fee structure caches cleared');
                break;

            case 'all':
            default:
                $this->cacheService->clearAllCaches();
                $this->info('✓ All caches cleared');
                break;
        }

        if ($warm) {
            $this->info('');
            $this->info('Warming up caches...');
            $this->cacheService->warmUpCaches();
            $this->info('✓ Caches warmed up');
        }

        // Display cache statistics
        $this->displayCacheStats();

        $this->newLine();
        $this->info('Cache operations completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Display cache statistics
     */
    protected function displayCacheStats(): void
    {
        $this->newLine();
        $this->info('Cache Status:');

        $stats = $this->cacheService->getCacheStats();

        $this->table(
            ['Cache Key', 'Status'],
            [
                ['Current Academic Year', $stats['current_academic_year'] ? '✓ Cached' : '✗ Not Cached'],
                ['Current Term', $stats['current_term'] ? '✓ Cached' : '✗ Not Cached'],
                ['Active Grades', $stats['active_grades'] ? '✓ Cached' : '✗ Not Cached'],
                ['Fee Structures', $stats['fee_structures_cached'] . ' term(s) cached'],
            ]
        );
    }
}
