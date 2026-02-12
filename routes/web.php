<?php

use App\Constants\RoleConstants;
use App\Http\Controllers\BusPassController;
use App\Http\Controllers\FeeStatementsController;
use App\Http\Controllers\HomeworkController;
use App\Http\Controllers\PaymentStatementController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\QuickGuideController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\StudentFeeController;
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
    if (! auth()->check()) {
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
        case RoleConstants::LIBRARIAN:
            return redirect('/admin/librarian-dashboard');
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

    // NEW: Individual transaction receipt
    Route::get('/student-fees/{fee}/transaction/{transaction}/receipt', [StudentFeeController::class, 'generateTransactionReceipt'])->name('student-fees.transaction-receipt');

    // NEW: Complete payment history
    Route::get('/student-fees/{studentFee}/full-history', [StudentFeeController::class, 'generateFullHistory'])->name('student-fees.full-history');

    // NEW: Export unpaid fees report
    Route::get('/student-fees/export-unpaid', [StudentFeeController::class, 'exportUnpaid'])->name('student-fees.export-unpaid');

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

    // Get homework details page (student-friendly view)
    Route::get('/homework/{homework}/details', [HomeworkController::class, 'details'])
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

// Payslip Routes
Route::middleware(['auth'])->group(function () {
    // View payslip in browser (HTML)
    Route::get('/payslips/{payroll}', [PayslipController::class, 'view'])
        ->name('payslips.view');

    // Stream payslip PDF in browser
    Route::get('/payslips/{payroll}/pdf', [PayslipController::class, 'stream'])
        ->name('payslips.stream');

    // Print payslip (streams PDF)
    Route::get('/payslips/{payroll}/print', [PayslipController::class, 'print'])
        ->name('payslips.print');

    // Download payslip as PDF
    Route::get('/payslips/{payroll}/download', [PayslipController::class, 'download'])
        ->name('payslips.download');
});

// Bus Pass Routes
Route::middleware(['auth'])->group(function () {
    // View bus pass in browser
    Route::get('/bus-passes/{busPayment}', [BusPassController::class, 'view'])
        ->name('bus-passes.view');

    // Print bus pass
    Route::get('/bus-passes/{busPayment}/print', [BusPassController::class, 'print'])
        ->name('bus-passes.print');

    // Download bus pass as PDF
    Route::get('/bus-passes/{busPayment}/download', [BusPassController::class, 'download'])
        ->name('bus-passes.download');

    // View payment receipt
    Route::get('/bus-receipts/{busPayment}', [BusPassController::class, 'receipt'])
        ->name('bus-receipts.view');
});

// Public Payment Routes (no authentication required)
Route::prefix('pay')->group(function () {
    Route::get('/', [PublicPaymentController::class, 'index'])->name('payment.index');
    Route::post('/search-student', [PublicPaymentController::class, 'searchStudent'])->name('payment.search-student');
    Route::post('/process', [PublicPaymentController::class, 'processPayment'])->name('payment.process');
    Route::post('/check-status', [PublicPaymentController::class, 'checkPaymentStatus'])->name('payment.check-status');
});

// Attendance Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/export', [\App\Http\Controllers\AttendanceController::class, 'export'])
        ->name('attendance.export');

    Route::get('/attendance/register/download', [\App\Http\Controllers\AttendanceRegisterController::class, 'download'])
        ->name('attendance.register.download');

    Route::get('/attendance/register/download-excel', [\App\Http\Controllers\AttendanceRegisterController::class, 'downloadExcel'])
        ->name('attendance.register.download-excel');
});

// Report Card Routes
Route::middleware(['auth'])->group(function () {
    // Generate single student report card PDF
    Route::get('/report-cards/{student}/{term}', [ReportCardController::class, 'generate'])
        ->name('report-cards.generate');

    // Preview report card (HTML view)
    Route::get('/report-cards/{student}/{term}/preview', [ReportCardController::class, 'preview'])
        ->name('report-cards.preview');

    // Bulk generate report cards (ZIP download)
    Route::get('/report-cards/bulk/{classSection}/{term}', [ReportCardController::class, 'bulkGenerate'])
        ->name('report-cards.bulk-generate');

    // API endpoint for report card data
    Route::get('/report-cards/{student}/{term}/data', [ReportCardController::class, 'getReportCardData'])
        ->name('report-cards.data');
});

// Quick Guide Routes
Route::middleware(['auth'])->group(function () {
    // View quick guide in browser (PDF viewer)
    Route::get('/quick-guide', [QuickGuideController::class, 'stream'])
        ->name('quick-guide.view');

    // Download quick guide as PDF
    Route::get('/quick-guide/download', [QuickGuideController::class, 'download'])
        ->name('quick-guide.download');
});

// Leave Application Routes
Route::middleware(['auth'])->group(function () {
    // View leave approval letter in browser (PDF viewer)
    Route::get('/leave-applications/{leaveApplication}/pdf', [\App\Http\Controllers\LeaveApplicationController::class, 'streamPdf'])
        ->name('leave-applications.pdf');

    // Download leave approval letter as PDF
    Route::get('/leave-applications/{leaveApplication}/download', [\App\Http\Controllers\LeaveApplicationController::class, 'downloadPdf'])
        ->name('leave-applications.download');
});

// Timetable Routes
Route::middleware(['auth'])->group(function () {
    // Print/Stream class timetable PDF
    Route::get('/timetable/class/{classSection}/{academicYear}', [\App\Http\Controllers\TimetableController::class, 'printClassTimetable'])
        ->name('timetable.print.class');

    // Download class timetable PDF
    Route::get('/timetable/class/{classSection}/{academicYear}/download', [\App\Http\Controllers\TimetableController::class, 'downloadClassTimetable'])
        ->name('timetable.download.class');

    // Print/Stream teacher schedule PDF
    Route::get('/timetable/teacher/{teacher}/{academicYear}', [\App\Http\Controllers\TimetableController::class, 'printTeacherSchedule'])
        ->name('timetable.print.teacher');

    // Download teacher schedule PDF
    Route::get('/timetable/teacher/{teacher}/{academicYear}/download', [\App\Http\Controllers\TimetableController::class, 'downloadTeacherSchedule'])
        ->name('timetable.download.teacher');

    // Print/Stream master timetable (all classes) PDF
    Route::get('/timetable/master/{academicYear}', [\App\Http\Controllers\TimetableController::class, 'printMasterTimetable'])
        ->name('timetable.print.master');

    // Download master timetable (all classes) PDF
    Route::get('/timetable/master/{academicYear}/download', [\App\Http\Controllers\TimetableController::class, 'downloadMasterTimetable'])
        ->name('timetable.download.master');
});

// Financial Report PDF Routes
Route::middleware(['auth'])->prefix('financial-reports')->group(function () {
    Route::get('/income-expense', [\App\Http\Controllers\FinancialReportController::class, 'incomeExpenseReport'])
        ->name('financial-reports.income-expense');

    Route::get('/cash-flow', [\App\Http\Controllers\FinancialReportController::class, 'cashFlowReport'])
        ->name('financial-reports.cash-flow');

    Route::get('/expense-detail', [\App\Http\Controllers\FinancialReportController::class, 'expenseDetailReport'])
        ->name('financial-reports.expense-detail');

    Route::get('/income-detail', [\App\Http\Controllers\FinancialReportController::class, 'incomeDetailReport'])
        ->name('financial-reports.income-detail');

    Route::get('/payables', [\App\Http\Controllers\FinancialReportController::class, 'payablesReport'])
        ->name('financial-reports.payables');
});

// Accountant User Guide
Route::middleware(['auth'])->prefix('guides')->group(function () {
    Route::get('/accountant', [\App\Http\Controllers\AccountantGuideController::class, 'show'])
        ->name('guides.accountant');
    Route::get('/accountant/download', [\App\Http\Controllers\AccountantGuideController::class, 'download'])
        ->name('guides.accountant.download');
    Route::get('/accountant/docx', [\App\Http\Controllers\AccountantGuideController::class, 'downloadDocx'])
        ->name('guides.accountant.docx');
});
