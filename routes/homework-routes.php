<?php

use App\Http\Controllers\HomeworkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Homework Routes
|--------------------------------------------------------------------------
|
| Routes for the homework workflow including file downloads and access
|
*/

// Routes for downloading homework and submission files
Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])
    ->name('homework.download')
    ->middleware(['auth']);

Route::get('/homework-submission/{submission}/download', [HomeworkController::class, 'downloadSubmission'])
    ->name('filament.resources.homework-submissions.download')
    ->middleware(['auth']);

// This file should be included in the main routes/web.php file using:
// require __DIR__.'/homework.php';
