<?php

use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\FeeStatementsController;
use App\Http\Controllers\PaymentStatementController;
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
    // Existing PDF receipt route (keep unchanged)
    Route::get('/student-fees/{studentFee}/receipt', [StudentFeeController::class, 'generateReceipt'])->name('student-fees.receipt');

    // NEW: HTML receipt view route
    Route::get('/student-fees/{studentFee}/receipt/view', [StudentFeeController::class, 'showReceipt'])->name('student-fees.receipt.view');

    // NEW: Explicit PDF download route
    Route::get('/student-fees/{studentFee}/receipt/pdf', [StudentFeeController::class, 'generateReceipt'])->name('student-fees.receipt.pdf');

    // Existing bulk receipts route (keep unchanged)
    Route::post('/student-fees/bulk-receipts', [StudentFeeController::class, 'generateBulkReceipts'])->name('student-fees.bulk-receipts');

    // NEW: Debug route (only for development - remove in production)
    Route::get('/debug/student-fee/{studentFee}', [StudentFeeController::class, 'debugFeeStructure'])->name('debug.student-fee');
});

// Enhanced Homework and Submission Routes
Route::middleware(['auth'])->group(function () {
    // Primary homework routes
    Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])
        ->name('homework.download');

    Route::get('/homework/{homework}/view', [HomeworkController::class, 'view'])
        ->name('homework.view');

    Route::get('/homework/{homework}', [HomeworkController::class, 'show'])
        ->name('homework.show');

    // Alternative download routes (for backward compatibility)
    Route::get('/homework/{homework}/download-file', [HomeworkController::class, 'downloadHomeworkFile'])
        ->name('homework.download-file');

    Route::get('/homework/{homework}/download-resources', [HomeworkController::class, 'downloadResources'])
        ->name('homework.download-resources');

    // Teacher-specific routes
    Route::get('/homework/{homework}/download-all-submissions', [HomeworkController::class, 'downloadAllSubmissions'])
        ->name('homework.download-all-submissions');

    // Submission routes
    Route::get('/homework-submissions/{submission}/download', [HomeworkController::class, 'downloadSubmission'])
        ->name('homework-submissions.download');

    Route::get('/filament/resources/homework-submissions/{record}/download', [HomeworkController::class, 'downloadSubmission'])
        ->name('filament.resources.homework-submissions.download');

    // API routes for homework
    Route::get('/homework/grade/{gradeId}', [HomeworkController::class, 'getHomeworkByGrade'])
        ->name('homework.by-grade');

    Route::get('/homework/stats', [HomeworkController::class, 'getHomeworkStats'])
        ->name('homework.stats');

    // Get homework details (API)
    Route::get('/homework/{homework}/details', [HomeworkController::class, 'show'])
        ->name('homework.details');
});

// Payment Statement Routes
Route::middleware(['auth'])->group(function () {
    // Generate payment statement for a student
    Route::get('/payment-statement/student/{student}', [PaymentStatementController::class, 'generateStatement'])
        ->name('payment-statement.generate');

    // Generate payment statement from a fee record
    Route::get('/payment-statement/fee/{studentFee}', [PaymentStatementController::class, 'generateFromFee'])
        ->name('payment-statement.from-fee');

    // Email payment statement
    Route::post('/payment-statement/student/{student}/email', [PaymentStatementController::class, 'emailStatement'])
        ->name('payment-statement.email');

    // Get payment statement summary (for AJAX/API)
    Route::get('/payment-statement/student/{student}/summary', [PaymentStatementController::class, 'getStatementSummary'])
        ->name('payment-statement.summary');

    // Bulk statement generation (for multiple students)
    Route::post('/payment-statements/bulk', [PaymentStatementController::class, 'generateBulkStatements'])
        ->name('payment-statements.bulk');
});
