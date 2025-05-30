<?php

use App\Http\Controllers\HomeworkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Homework Routes
|--------------------------------------------------------------------------
|
| Enhanced routes for the homework workflow including file downloads,
| viewing, and access control with proper permission checks
|
*/

// Main homework file access routes
Route::middleware(['auth'])->group(function () {

    // Primary download route - secure file download with access control
    Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])
        ->name('homework.download');

    // View homework file in browser (PDF viewer, etc.)
    Route::get('/homework/{homework}/view', [HomeworkController::class, 'view'])
        ->name('homework.view');

    // Get homework details (JSON API)
    Route::get('/homework/{homework}/details', [HomeworkController::class, 'show'])
        ->name('homework.details');

    // Alternative download route (for backward compatibility)
    Route::get('/homework/{homework}/download-file', [HomeworkController::class, 'downloadHomeworkFile'])
        ->name('homework.download-file');

    // Download homework resources (for future expansion)
    Route::get('/homework/{homework}/download-resources', [HomeworkController::class, 'downloadResources'])
        ->name('homework.download-resources');

    // Teacher-specific routes
    Route::get('/homework/{homework}/download-all-submissions', [HomeworkController::class, 'downloadAllSubmissions'])
        ->name('homework.download-all-submissions');

    // Homework submission download routes
    Route::get('/homework-submissions/{submission}/download', [HomeworkController::class, 'downloadSubmission'])
        ->name('homework-submissions.download');

    // Filament-specific submission download route
    Route::get('/filament/resources/homework-submissions/{record}/download', [HomeworkController::class, 'downloadSubmission'])
        ->name('filament.resources.homework-submissions.download');

    // API routes for mobile/web app integration
    Route::get('/homework/grade/{gradeId}', [HomeworkController::class, 'getHomeworkByGrade'])
        ->name('homework.by-grade');

    Route::get('/homework/stats', [HomeworkController::class, 'getHomeworkStats'])
        ->name('homework.stats');
});

/*
|--------------------------------------------------------------------------
| Route Usage Notes
|--------------------------------------------------------------------------
|
| This file should be included in the main routes/web.php file using:
| require __DIR__.'/homework-routes.php';
|
| Or you can copy these routes directly into routes/web.php
|
| Routes are protected by 'auth' middleware and include:
| - Role-based access control (Admin, Teacher, Parent, Student)
| - File existence validation
| - Secure file download with proper headers
| - Download/view activity logging
| - API endpoints for mobile applications
|
*/
