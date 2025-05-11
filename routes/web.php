<?php

use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\FeeStatementsController;
use App\Constants\RoleConstants;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Test route to make sure routing works
Route::get('/test', function () {
    return 'Routes are working!';
});

// Root route - redirect to login if not authenticated, otherwise to dashboard
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect('/admin/login');
    }

    $user = auth()->user();

    switch ($user->role_id) {
        case RoleConstants::TEACHER:
            return redirect('/admin/teacher-dashboard');
        case RoleConstants::PARENT:
            return redirect('/admin/parent-dashboard');
        case RoleConstants::STUDENT:
            return redirect('/admin/student-dashboard');
        case RoleConstants::ADMIN:
        default:
            return redirect('/admin');
    }
});

// Dashboard route - alias for backward compatibility
Route::get('/dashboard', function () {
    return redirect('/');
})->name('dashboard');

// Home route
Route::get('/home', function () {
    return redirect('/');
})->name('home');

// Don't intercept /admin - let Filament handle it naturally
// This includes /admin/login

// Fee Statements Routes
Route::prefix('fee-statements')->middleware(['auth'])->group(function () {
    Route::get('/', [FeeStatementsController::class, 'index'])->name('fee-statements.index');
    Route::post('/generate', [FeeStatementsController::class, 'generate'])->name('fee-statements.generate');
    Route::post('/summary', [FeeStatementsController::class, 'summary'])->name('fee-statements.summary');
});

// Student Fees Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/student-fees/{studentFee}/receipt', [StudentFeeController::class, 'generateReceipt'])->name('student-fees.receipt');
    Route::post('/student-fees/bulk-receipts', [StudentFeeController::class, 'generateBulkReceipts'])->name('student-fees.bulk-receipts');
});

// Homework and Submission Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])->name('homework.download');
    Route::get('/homework/{homework}', [HomeworkController::class, 'show'])->name('homework.show');
    Route::get('/homework/{homework}/download-file', [HomeworkController::class, 'downloadHomeworkFile'])->name('homework.download-file');
    Route::get('/homework/{homework}/download-resources', [HomeworkController::class, 'downloadResources'])->name('homework.download-resources');
    Route::get('/homework/{homework}/download-all-submissions', [HomeworkController::class, 'downloadAllSubmissions'])->name('homework.download-all-submissions');
    Route::get('/homework-submissions/{submission}/download', [HomeworkController::class, 'downloadSubmission'])->name('homework-submissions.download');
    Route::get('/filament/resources/homework-submissions/{record}/download', [HomeworkController::class, 'downloadSubmission'])->name('filament.resources.homework-submissions.download');
});
