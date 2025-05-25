<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Services\BalanceForwardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class PaymentStatementController extends Controller
{
    protected BalanceForwardService $balanceService;

    public function __construct(BalanceForwardService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Generate payment statement for a student
     */
    public function generateStatement(Student $student, Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $termId = $request->input('term_id');
        $format = $request->input('format', 'pdf'); // pdf or html

        // Get comprehensive payment data
        $statementData = $this->prepareStatementData($student, $academicYearId, $termId);

        if ($format === 'html') {
            return view('statements.payment-statement', $statementData);
        }

        // Generate PDF
        $pdf = Pdf::loadView('statements.payment-statement', $statementData);

        // Configure PDF settings
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('margin-top', 15);
        $pdf->setOption('margin-right', 15);
        $pdf->setOption('margin-bottom', 15);
        $pdf->setOption('margin-left', 15);
        $pdf->setOption('dpi', 150);
        $pdf->setOption('isRemoteEnabled', true);

        $filename = $this->generateFileName($student, $statementData['period_description']);

        return $pdf->stream($filename);
    }

    /**
     * Generate payment statement from StudentFee record
     */
    public function generateFromFee(StudentFee $studentFee, Request $request)
    {
        return $this->generateStatement(
            $studentFee->student,
            $request->merge([
                'academic_year_id' => $studentFee->academic_year_id,
                'term_id' => $studentFee->term_id
            ])
        );
    }

    /**
     * Prepare comprehensive statement data
     */
    private function prepareStatementData(Student $student, ?int $academicYearId = null, ?int $termId = null): array
    {
        // Get payment history
        $history = $this->balanceService->getPaymentHistory($student, $academicYearId);

        // Filter by term if specified
        if ($termId) {
            $history = array_filter($history, function($record) use ($termId) {
                return isset($record['term_id']) && $record['term_id'] == $termId;
            });
        }

        // Calculate totals
        $totalFees = collect($history)->sum('total_fee');
        $totalPaid = collect($history)->sum('amount_paid');
        $totalOutstanding = collect($history)->sum('balance');
        $collectionRate = $totalFees > 0 ? ($totalPaid / $totalFees) * 100 : 0;

        // Get period information
        $periodInfo = $this->getPeriodInfo($academicYearId, $termId);

        // Get parent/guardian information
        $parentGuardian = $student->parentGuardian;

        // Get current academic year and term info
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $currentTerm = Term::where('is_active', true)->first();

        return [
            'student' => $student,
            'parent_guardian' => $parentGuardian,
            'payment_history' => $history,
            'summary' => [
                'total_fees_charged' => $totalFees,
                'total_payments_made' => $totalPaid,
                'total_outstanding' => $totalOutstanding,
                'collection_rate' => $collectionRate,
                'number_of_terms' => count($history),
            ],
            'period_info' => $periodInfo,
            'period_description' => $periodInfo['description'],
            'current_academic_year' => $currentAcademicYear,
            'current_term' => $currentTerm,
            'generated_at' => now(),
            'statement_number' => $this->generateStatementNumber($student),
            'school_info' => $this->getSchoolInfo(),
        ];
    }

    /**
     * Get period information for the statement
     */
    private function getPeriodInfo(?int $academicYearId, ?int $termId): array
    {
        if ($termId) {
            $term = Term::with('academicYear')->find($termId);
            return [
                'type' => 'term',
                'description' => "{$term->name} - {$term->academicYear->name}",
                'start_date' => $term->start_date,
                'end_date' => $term->end_date,
                'term' => $term,
                'academic_year' => $term->academicYear,
            ];
        } elseif ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
            return [
                'type' => 'academic_year',
                'description' => $academicYear->name,
                'start_date' => $academicYear->start_date,
                'end_date' => $academicYear->end_date,
                'academic_year' => $academicYear,
            ];
        } else {
            return [
                'type' => 'complete',
                'description' => 'Complete Payment History',
                'start_date' => null,
                'end_date' => null,
            ];
        }
    }

    /**
     * Generate unique statement number
     */
    private function generateStatementNumber(Student $student): string
    {
        $year = date('Y');
        $month = date('m');
        $studentId = str_pad($student->id, 4, '0', STR_PAD_LEFT);
        $timestamp = date('His');

        return "STMT-{$year}{$month}-{$studentId}-{$timestamp}";
    }

    /**
     * Generate filename for the statement
     */
    private function generateFileName(Student $student, string $period): string
    {
        $studentName = str_replace(' ', '_', $student->name);
        $periodName = str_replace([' ', '/', '\\'], '_', $period);
        $date = date('Y-m-d');

        return "Payment_Statement_{$studentName}_{$periodName}_{$date}.pdf";
    }

    /**
     * Get school information for the statement header
     */
    private function getSchoolInfo(): array
    {
        return [
            'name' => 'St. Francis Of Assisi Private School',
            'address' => 'Plot No 1310/4 East Kamenza, Chililabombwe, Zambia',
            'phone' => '+260 972 266 217',
            'email' => 'info@stfrancisofassisi.tech',
            'logo_path' => public_path('images/logo.png'),
        ];
    }

    /**
     * Send statement via email (optional feature)
     */
    public function emailStatement(Student $student, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);

        $statementData = $this->prepareStatementData(
            $student,
            $request->input('academic_year_id'),
            $request->input('term_id')
        );

        $pdf = Pdf::loadView('statements.payment-statement', $statementData);
        $filename = $this->generateFileName($student, $statementData['period_description']);

        // Here you would integrate with your email service
        // This is a placeholder for the actual email implementation

        return response()->json([
            'message' => 'Payment statement has been sent to ' . $request->input('email'),
            'statement_number' => $statementData['statement_number']
        ]);
    }

    /**
     * Get statement summary for API/AJAX requests
     */
    public function getStatementSummary(Student $student, Request $request)
    {
        $academicYearId = $request->input('academic_year_id');
        $termId = $request->input('term_id');

        $statementData = $this->prepareStatementData($student, $academicYearId, $termId);

        return response()->json([
            'student' => [
                'name' => $student->name,
                'student_id' => $student->student_id_number,
                'grade' => $student->grade->name ?? 'Not assigned',
            ],
            'summary' => $statementData['summary'],
            'period' => $statementData['period_description'],
            'statement_number' => $statementData['statement_number'],
        ]);
    }
}
