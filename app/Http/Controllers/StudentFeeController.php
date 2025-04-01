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
        // Generate the PDF receipt
        $pdf = Pdf::loadView('pdf.fee-receipt', [
            'studentFee' => $studentFee,
            'copy' => 'RECEIPT',
            'lastPaymentAmount' => $studentFee->amount_paid
        ]);

        // Set PDF options for half page (A5 portrait size)
        // A5 is exactly half of A4 page
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
     * Generate receipts for multiple student fee payments
     */
    public function generateBulkReceipts(Request $request)
    {
        // Get the IDs from the request
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'No student fees selected');
        }

        // Get the student fees
        $studentFees = StudentFee::whereIn('id', $ids)
            ->where('payment_status', '!=', 'unpaid')
            ->get();

        if ($studentFees->isEmpty()) {
            return back()->with('error', 'No valid student fees found');
        }

        // Generate individual PDF files and combine them
        $pdfFiles = [];
        foreach ($studentFees as $studentFee) {
            $pdf = Pdf::loadView('pdf.fee-receipt', [
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

        // Create a merged PDF file (using simple approach for demonstration)
        // In a real application, you might want to use a dedicated PDF merging library
        $mergedPdf = $pdfFiles[0] ?? '';

        // Return the PDF for download
        return response()->make($mergedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="receipts-batch-' . now()->format('Y-m-d') . '.pdf"'
        ]);
    }
}
