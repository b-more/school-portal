<?php

namespace App\Http\Controllers;

use App\Models\StudentFee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentFeeController extends Controller
{
    /**
     * Generate a receipt for a student fee payment
     */
    public function generateReceipt(StudentFee $studentFee)
    {
        // Load all necessary relationships to prevent null errors
        $studentFee->load([
            'student' => function($query) {
                $query->with(['grade', 'parentGuardian', 'classSection']);
            },
            'feeStructure' => function($query) {
                $query->with(['grade', 'term', 'academicYear']);
            },
            'grade',
            'term',
            'academicYear'
        ]);

        // If fee structure is still null, try to find it manually
        if (!$studentFee->feeStructure && $studentFee->fee_structure_id) {
            $feeStructure = \App\Models\FeeStructure::with(['grade', 'term', 'academicYear'])
                ->find($studentFee->fee_structure_id);

            if ($feeStructure) {
                $studentFee->setRelation('feeStructure', $feeStructure);
            }
        }

        // Generate the PDF receipt - Using the correct view path
        $pdf = Pdf::loadView('fee-receipt', [
            'studentFee' => $studentFee,
            'copy' => 'RECEIPT',
            'lastPaymentAmount' => $studentFee->amount_paid
        ]);

        // Set PDF options for half page (A5 portrait size)
        $pdf->setPaper('a5', 'portrait');

        // Set smaller margins to maximize usable space
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-right', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);

        // Ensure better quality and image loading
        $pdf->setOption('dpi', 150);
        $pdf->setOption('isRemoteEnabled', true);

        // Return the PDF for download
        return $pdf->stream("receipt-{$studentFee->receipt_number}.pdf");
    }

    /**
     * Show receipt in browser (HTML version)
     */
    // public function showReceipt(StudentFee $studentFee)
    // {
    //     // Load all necessary relationships
    //     $studentFee->load([
    //         'student' => function($query) {
    //             $query->with(['grade', 'parentGuardian', 'classSection']);
    //         },
    //         'feeStructure' => function($query) {
    //             $query->with(['grade', 'term', 'academicYear']);
    //         },
    //         'grade',
    //         'term',
    //         'academicYear'
    //     ]);

    //     // If fee structure is still null, try to find it manually
    //     if (!$studentFee->feeStructure && $studentFee->fee_structure_id) {
    //         $feeStructure = \App\Models\FeeStructure::with(['grade', 'term', 'academicYear'])
    //             ->find($studentFee->fee_structure_id);

    //         if ($feeStructure) {
    //             $studentFee->setRelation('feeStructure', $feeStructure);
    //         }
    //     }

    //     // Return HTML view
    //     return view('fee-receipt', [
    //         'studentFee' => $studentFee,
    //         'lastPaymentAmount' => $studentFee->amount_paid
    //     ]);
    // }

    /**
     * Generate receipts for multiple student fee payments
     */
    public function generateBulkReceipts(Request $request)
    {
        // Get the IDs from the request
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No student fees selected');
        }

        // Get the student fees with relationships
        $studentFees = StudentFee::whereIn('id', $ids)
            ->where('payment_status', '!=', 'unpaid')
            ->with([
                'student' => function($query) {
                    $query->with(['grade', 'parentGuardian', 'classSection']);
                },
                'feeStructure' => function($query) {
                    $query->with(['grade', 'term', 'academicYear']);
                },
                'grade',
                'term',
                'academicYear'
            ])
            ->get();

        if ($studentFees->isEmpty()) {
            return back()->with('error', 'No valid student fees found');
        }

        // Generate individual PDF files and combine them
        $pdfFiles = [];
        foreach ($studentFees as $studentFee) {
            // Manual fee structure loading if needed
            if (!$studentFee->feeStructure && $studentFee->fee_structure_id) {
                $feeStructure = \App\Models\FeeStructure::with(['grade', 'term', 'academicYear'])
                    ->find($studentFee->fee_structure_id);

                if ($feeStructure) {
                    $studentFee->setRelation('feeStructure', $feeStructure);
                }
            }

            $pdf = Pdf::loadView('fee-receipt', [
                'studentFee' => $studentFee,
                'copy' => 'RECEIPT',
                'lastPaymentAmount' => $studentFee->amount_paid
            ]);

            // Set PDF options same as individual receipts
            $pdf->setPaper('a5', 'portrait');
            $pdf->setOption('margin-top', 10);
            $pdf->setOption('margin-right', 10);
            $pdf->setOption('margin-bottom', 10);
            $pdf->setOption('margin-left', 10);
            $pdf->setOption('dpi', 150);
            $pdf->setOption('isRemoteEnabled', true);

            $pdfFiles[] = $pdf->output();
        }

        // Create a merged PDF file
        $mergedPdf = $pdfFiles[0] ?? '';

        // Return the PDF for download
        return response()->make($mergedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipts-batch-' . now()->format('Y-m-d') . '.pdf"'
        ]);
    }

    public function showReceipt(StudentFee $studentFee)
{
    // Load all necessary relationships to prevent null errors
    $studentFee->load([
        'student' => function($query) {
            $query->with(['grade', 'parentGuardian', 'classSection']);
        },
        'feeStructure' => function($query) {
            $query->with(['grade', 'term', 'academicYear']);
        },
        'grade',
        'term',
        'academicYear'
    ]);

    // Debug logging
    \Log::info('Receipt HTML View Debug', [
        'student_fee_id' => $studentFee->id,
        'fee_structure_id' => $studentFee->fee_structure_id,
        'has_fee_structure' => !is_null($studentFee->feeStructure),
        'student_id' => $studentFee->student_id,
        'has_student' => !is_null($studentFee->student),
    ]);

    // If fee structure is still null, try to find it manually
    if (!$studentFee->feeStructure && $studentFee->fee_structure_id) {
        $feeStructure = \App\Models\FeeStructure::with(['grade', 'term', 'academicYear'])
            ->find($studentFee->fee_structure_id);

        if ($feeStructure) {
            $studentFee->setRelation('feeStructure', $feeStructure);
            \Log::info('Manually loaded fee structure for HTML view');
        } else {
            \Log::warning('Fee structure not found', ['fee_structure_id' => $studentFee->fee_structure_id]);
        }
    }

    // Return HTML view (make sure this file exists at resources/views/fee-receipt.blade.php)
    return view('fee-receipt', [
        'studentFee' => $studentFee,
        'lastPaymentAmount' => $studentFee->amount_paid
    ]);
}

/**
 * Debug method to check fee structure relationships
 */
public function debugFeeStructure(StudentFee $studentFee)
{
    // Load relationships
    $studentFee->load([
        'student.grade',
        'feeStructure.grade',
        'feeStructure.term',
        'feeStructure.academicYear'
    ]);

    $debug = [
        'student_fee' => [
            'id' => $studentFee->id,
            'student_id' => $studentFee->student_id,
            'fee_structure_id' => $studentFee->fee_structure_id,
            'academic_year_id' => $studentFee->academic_year_id,
            'term_id' => $studentFee->term_id,
            'grade_id' => $studentFee->grade_id,
            'payment_status' => $studentFee->payment_status,
            'amount_paid' => $studentFee->amount_paid,
            'balance' => $studentFee->balance,
        ],
        'has_fee_structure' => !is_null($studentFee->feeStructure),
        'has_student' => !is_null($studentFee->student),
    ];

    // Manual check for fee structure
    if ($studentFee->fee_structure_id) {
        $feeStructure = \App\Models\FeeStructure::find($studentFee->fee_structure_id);
        $debug['fee_structure_exists_in_db'] = !is_null($feeStructure);

        if ($feeStructure) {
            $debug['fee_structure_data'] = [
                'id' => $feeStructure->id,
                'grade_id' => $feeStructure->grade_id,
                'term_id' => $feeStructure->term_id,
                'academic_year_id' => $feeStructure->academic_year_id,
                'total_fee' => $feeStructure->total_fee,
                'basic_fee' => $feeStructure->basic_fee,
                'is_active' => $feeStructure->is_active,
            ];
        }
    } else {
        $debug['fee_structure_id_is_null'] = true;
    }

    // Manual check for student
    if ($studentFee->student_id) {
        $student = \App\Models\Student::find($studentFee->student_id);
        $debug['student_exists_in_db'] = !is_null($student);

        if ($student) {
            $debug['student_data'] = [
                'id' => $student->id,
                'name' => $student->name,
                'grade_id' => $student->grade_id,
                'student_id_number' => $student->student_id_number,
            ];
        }
    }

    return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
}

    /**
     * Debug method to check fee structure relationships
     */
    // public function debugFeeStructure(StudentFee $studentFee)
    // {
    //     // Load relationships
    //     $studentFee->load([
    //         'student.grade',
    //         'feeStructure.grade',
    //         'feeStructure.term',
    //         'feeStructure.academicYear'
    //     ]);

    //     $debug = [
    //         'student_fee' => [
    //             'id' => $studentFee->id,
    //             'student_id' => $studentFee->student_id,
    //             'fee_structure_id' => $studentFee->fee_structure_id,
    //             'academic_year_id' => $studentFee->academic_year_id,
    //             'term_id' => $studentFee->term_id,
    //             'grade_id' => $studentFee->grade_id,
    //             'payment_status' => $studentFee->payment_status,
    //             'amount_paid' => $studentFee->amount_paid,
    //             'balance' => $studentFee->balance,
    //         ],
    //         'has_fee_structure' => !is_null($studentFee->feeStructure),
    //         'has_student' => !is_null($studentFee->student),
    //     ];

    //     // Manual check for fee structure
    //     if ($studentFee->fee_structure_id) {
    //         $feeStructure = \App\Models\FeeStructure::find($studentFee->fee_structure_id);
    //         $debug['fee_structure_exists_in_db'] = !is_null($feeStructure);

    //         if ($feeStructure) {
    //             $debug['fee_structure_data'] = [
    //                 'id' => $feeStructure->id,
    //                 'grade_id' => $feeStructure->grade_id,
    //                 'term_id' => $feeStructure->term_id,
    //                 'academic_year_id' => $feeStructure->academic_year_id,
    //                 'total_fee' => $feeStructure->total_fee,
    //                 'basic_fee' => $feeStructure->basic_fee,
    //                 'is_active' => $feeStructure->is_active,
    //             ];
    //         }
    //     }

    //     // Manual check for student
    //     if ($studentFee->student_id) {
    //         $student = \App\Models\Student::find($studentFee->student_id);
    //         $debug['student_exists_in_db'] = !is_null($student);

    //         if ($student) {
    //             $debug['student_data'] = [
    //                 'id' => $student->id,
    //                 'name' => $student->name,
    //                 'grade_id' => $student->grade_id,
    //                 'student_id_number' => $student->student_id_number,
    //             ];
    //         }
    //     }

    //     return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    // }
}
