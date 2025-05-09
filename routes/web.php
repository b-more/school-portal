<?php

use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\FeeStatementsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::prefix('fee-statements')->group(function () {
    Route::get('/', [FeeStatementsController::class, 'index'])->name('fee-statements.index');
    Route::post('/generate', [FeeStatementsController::class, 'generate'])->name('fee-statements.generate');
    Route::post('/summary', [FeeStatementsController::class, 'summary'])->name('fee-statements.summary');
});

Route::get('/student-fees/{studentFee}/receipt', [StudentFeeController::class, 'generateReceipt'])->name('student-fees.receipt');
Route::post('/student-fees/bulk-receipts', [StudentFeeController::class, 'generateBulkReceipts'])->name('student-fees.bulk-receipts');

Route::get('/homework/{homework}/download', [HomeworkController::class, 'download'])
    ->name('homework.download')
    ->middleware(['auth']);

Route::get('/homework-submission/{submission}/download', [HomeworkController::class, 'downloadSubmission'])
    ->name('filament.resources.homework-submissions.download')
    ->middleware(['auth']);

// Student Fees Routes
Route::get('/student-fees/{studentFee}/receipt', [StudentFeeController::class, 'generateReceipt'])->name('student-fees.receipt');
Route::post('/student-fees/bulk-receipts', [StudentFeeController::class, 'generateBulkReceipts'])->name('student-fees.bulk-receipts');

// Homework and Submission Routes
Route::get('/homework/{homework}/download-file', [HomeworkController::class, 'downloadHomeworkFile'])->name('homework.download-file');
Route::get('/homework/{homework}/download-resources', [HomeworkController::class, 'downloadResources'])->name('homework.download-resources');
Route::get('/homework/{homework}/download-all-submissions', [HomeworkController::class, 'downloadAllSubmissions'])->name('homework.download-all-submissions');
Route::get('/homework-submissions/{submission}/download', [HomeworkController::class, 'downloadSubmission'])->name('homework-submissions.download');

// Add route for filament resource download
Route::get('/filament/resources/homework-submissions/{record}/download', [HomeworkController::class, 'downloadSubmission'])
    ->name('filament.resources.homework-submissions.download')
    ->middleware(['web', 'auth']);
