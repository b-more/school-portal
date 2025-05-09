<?php

namespace App\Http\Controllers;

use App\Models\StudentFee;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\SchoolSection;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class FeeStatementsController extends Controller
{
    /**
     * Show the statement generation form
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::orderBy('name')->get();
        $grades = Grade::orderBy('level')->get();
        $sections = SchoolSection::orderBy('name')->get();
        $students = Student::where('enrollment_status', 'active')->orderBy('name')->get();

        return view('statements.index', compact('academicYears', 'terms', 'grades', 'sections', 'students'));
    }

    /**
     * Generate a fee statement based on filters
     */
    public function generate(Request $request)
    {
        // Validate request
        $request->validate([
            'report_type' => 'required|in:individual,grade,section,term,all',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'grade_id' => 'nullable|exists:grades,id',
            'section_id' => 'nullable|exists:school_sections,id',
            'student_id' => 'nullable|exists:students,id',
        ]);

        // Build query based on filters
        $query = StudentFee::query()
            ->with(['student', 'feeStructure', 'feeStructure.grade', 'feeStructure.term'])
            ->where('academic_year_id', $request->academic_year_id);

        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
        }

        // Apply filters based on report type
        switch ($request->report_type) {
            case 'individual':
                if (!$request->student_id) {
                    return back()->with('error', 'Please select a student for individual statement.');
                }
                $query->where('student_id', $request->student_id);
                break;

            case 'grade':
                if (!$request->grade_id) {
                    return back()->with('error', 'Please select a grade for grade statement.');
                }
                // Join with students table to filter by grade_id
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('grade_id', $request->grade_id);
                });
                break;

            case 'section':
                if (!$request->section_id) {
                    return back()->with('error', 'Please select a section for section statement.');
                }
                // Join with students table and grades to filter by section_id
                $query->whereHas('student', function ($q) use ($request) {
                    $q->whereHas('grade', function ($q2) use ($request) {
                        $q2->where('school_section_id', $request->section_id);
                    });
                });
                break;

            case 'term':
                if (!$request->term_id) {
                    return back()->with('error', 'Please select a term for term statement.');
                }
                // Already filtered by term_id above
                break;
        }

        // Get results
        $studentFees = $query->get();

        // Get report title and additional data
        $reportData = $this->getReportData($request, $studentFees);

        // Load the appropriate view based on report type
        $viewName = 'statements.' . $request->report_type;

        // Check if we need to render a PDF
        if ($request->has('pdf')) {
            $pdf = PDF::loadView($viewName, [
                'studentFees' => $studentFees,
                'reportData' => $reportData,
            ]);

            // Stream (display in browser) or download
            if ($request->has('download')) {
                return $pdf->download("fee_statement_{$reportData['title']}.pdf");
            } else {
                return $pdf->stream("fee_statement_{$reportData['title']}.pdf");
            }
        } else {
            // Just render HTML view
            return view($viewName, [
                'studentFees' => $studentFees,
                'reportData' => $reportData,
            ]);
        }
    }

    /**
     * Get report data based on filters
     */
    private function getReportData(Request $request, $studentFees)
    {
        $data = [
            'title' => 'Fee Statement',
            'subtitle' => '',
            'date' => now()->format('F j, Y'),
            'totalFees' => $studentFees->sum('feeStructure.total_fee'),
            'totalPaid' => $studentFees->sum('amount_paid'),
            'totalBalance' => $studentFees->sum('balance'),
            'count' => $studentFees->count(),
        ];

        $academicYear = AcademicYear::find($request->academic_year_id);
        $data['academicYear'] = $academicYear->name ?? 'Unknown';

        switch ($request->report_type) {
            case 'individual':
                $student = Student::find($request->student_id);
                $data['title'] = "Individual Fee Statement - {$student->name}";
                $data['subtitle'] = "Student: {$student->name}";
                $data['student'] = $student;
                break;

            case 'grade':
                $grade = Grade::find($request->grade_id);
                $data['title'] = "Grade Fee Statement - {$grade->name}";
                $data['subtitle'] = "Grade: {$grade->name}";
                $data['grade'] = $grade;
                break;

            case 'section':
                $section = SchoolSection::find($request->section_id);
                $data['title'] = "Section Fee Statement - {$section->name}";
                $data['subtitle'] = "Section: {$section->name}";
                $data['section'] = $section;
                break;

            case 'term':
                $term = Term::find($request->term_id);
                $data['title'] = "Term Fee Statement - {$term->name}";
                $data['subtitle'] = "Term: {$term->name}";
                $data['term'] = $term;
                break;

            case 'all':
                $data['title'] = "Complete Fee Statement";
                $data['subtitle'] = "All Students";
                break;
        }

        // Add term information if selected
        if ($request->term_id && $request->report_type !== 'term') {
            $term = Term::find($request->term_id);
            $data['subtitle'] .= " | Term: {$term->name}";
            $data['term'] = $term;
        }

        return $data;
    }

    /**
     * Generate payment summary statistics
     */
    public function summary(Request $request)
    {
        // Validate request
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
        ]);

        $academicYear = AcademicYear::find($request->academic_year_id);

        // Base query
        $query = StudentFee::where('academic_year_id', $request->academic_year_id);

        if ($request->term_id) {
            $query->where('term_id', $request->term_id);
            $term = Term::find($request->term_id);
        }

        // Get overall statistics
        $totalStats = [
            'total_fees' => $query->sum(DB::raw('feeStructure.total_fee')),
            'total_paid' => $query->sum('amount_paid'),
            'total_balance' => $query->sum('balance'),
            'count' => $query->count(),
        ];

        // Get statistics by payment status
        $statusStats = $query->select('payment_status',
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount_paid) as total_paid'),
            DB::raw('SUM(balance) as total_balance'))
            ->groupBy('payment_status')
            ->get();

        // Get statistics by grade
        $gradeStats = $query->join('students', 'student_fees.student_id', '=', 'students.id')
            ->join('grades', 'students.grade_id', '=', 'grades.id')
            ->select('grades.name as grade_name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(student_fees.amount_paid) as total_paid'),
                DB::raw('SUM(student_fees.balance) as total_balance'))
            ->groupBy('grades.name')
            ->orderBy('grades.level')
            ->get();

        // Get payment methods statistics
        $paymentMethodStats = $query->whereNotNull('payment_method')
            ->select('payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount_paid) as total_paid'))
            ->groupBy('payment_method')
            ->get();

        // Prepare report data
        $reportData = [
            'title' => 'Fee Payment Summary',
            'subtitle' => "Academic Year: {$academicYear->name}",
            'academicYear' => $academicYear,
            'term' => $term ?? null,
            'date' => now()->format('F j, Y'),
            'totalStats' => $totalStats,
            'statusStats' => $statusStats,
            'gradeStats' => $gradeStats,
            'paymentMethodStats' => $paymentMethodStats,
        ];

        if (isset($term)) {
            $reportData['subtitle'] .= " | Term: {$term->name}";
        }

        // Check if we need to render a PDF
        if ($request->has('pdf')) {
            $pdf = PDF::loadView('statements.summary', [
                'reportData' => $reportData,
            ]);

            // Stream (display in browser) or download
            if ($request->has('download')) {
                return $pdf->download("fee_summary_{$academicYear->name}.pdf");
            } else {
                return $pdf->stream("fee_summary_{$academicYear->name}.pdf");
            }
        } else {
            // Just render HTML view
            return view('statements.summary', [
                'reportData' => $reportData,
            ]);
        }
    }
}
