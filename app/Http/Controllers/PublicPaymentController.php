<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFee;
use App\Models\QrPayment;
use App\Services\CGrateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPaymentController extends Controller
{
    /**
     * Show the payment form
     */
    public function index()
    {
        return view('payment.index');
    }

    /**
     * Search for student by ID or name
     */
    public function searchStudent(Request $request)
    {
        $search = $request->input('search');

        $student = Student::with(['grade', 'parentGuardian'])
            ->where('student_id_number', $search)
            ->orWhere('name', 'like', "%{$search}%")
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found. Please check the Student ID or Name.'
            ], 404);
        }

        // Get current academic year and term
        $currentAcademicYear = \App\Models\AcademicYear::where('is_current', true)->first();
        $currentTerm = \App\Models\Term::where('is_current', true)->first();

        // Get student's fees for current term (or all if no current term set)
        $feesQuery = StudentFee::with(['feeStructure'])->where('student_id', $student->id);

        if ($currentTerm) {
            $feesQuery->where('term_id', $currentTerm->id);
        }
        if ($currentAcademicYear) {
            $feesQuery->where('academic_year_id', $currentAcademicYear->id);
        }

        $fees = $feesQuery->get();

        // Calculate totals using fee_structure relationship
        $totalAmount = $fees->sum(function($fee) {
            return $fee->feeStructure?->total_fee ?? 0;
        });
        $totalPaid = $fees->sum('amount_paid');
        $balance = $fees->sum('balance');

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'student_id' => $student->student_id_number,
                'name' => $student->name,
                'grade' => $student->grade?->name ?? 'N/A',
                'parent_mobile' => $student->parentGuardian?->phone ?? '',
                'academic_year' => $currentAcademicYear?->name ?? date('Y'),
                'term' => $currentTerm?->name ?? 'N/A',
                'total_amount' => $totalAmount,
                'amount_paid' => $totalPaid,
                'balance' => $balance,
            ]
        ]);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1',
            'mobile_number' => 'required|string',
        ]);

        $student = Student::findOrFail($request->student_id);
        $amount = $request->amount;
        $mobile = $request->mobile_number;

        // Generate payment reference
        $paymentReference = 'QR-' . strtoupper(Str::random(10));

        // Generate QR code string
        $qrCodeData = QrPayment::generateQrCode($paymentReference, $amount, $mobile);

        // Create QR payment record
        $qrPayment = QrPayment::create([
            'qr_code' => $qrCodeData,
            'payment_reference' => $paymentReference,
            'amount' => $amount,
            'customer_mobile' => $mobile,
            'student_id' => $student->id,
            'status' => 'pending',
            'initiated_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        // Initiate CGrate payment
        $cgrateService = new CGrateService();
        $result = $cgrateService->processCustomerPayment($amount, $mobile, $paymentReference);

        if ($result['success']) {
            $qrPayment->update([
                'status' => 'processing',
                'cgrate_payment_id' => $result['paymentId'] ?? null,
                'response_message' => $result['message'] ?? 'Payment initiated',
                'response_code' => $result['responseCode'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully. Please check your phone to complete the payment.',
                'payment_reference' => $paymentReference,
                'qr_payment_id' => $qrPayment->id,
            ]);
        } else {
            $qrPayment->update([
                'status' => 'failed',
                'response_message' => $result['message'] ?? 'Payment initiation failed',
                'response_code' => $result['responseCode'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment initiation failed. Please try again.',
            ], 422);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(Request $request)
    {
        $paymentId = $request->input('payment_id');

        $qrPayment = QrPayment::findOrFail($paymentId);

        if ($qrPayment->status === 'completed') {
            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => 'Payment completed successfully!',
                'transaction_id' => $qrPayment->cgrate_payment_id,
                'payment_reference' => $qrPayment->payment_reference,
                'amount' => $qrPayment->amount,
                'mobile_number' => $qrPayment->customer_mobile,
                'completed_at' => $qrPayment->completed_at?->format('Y-m-d H:i:s'),
            ]);
        }

        if ($qrPayment->status === 'failed') {
            return response()->json([
                'success' => true,
                'status' => 'failed',
                'message' => $qrPayment->response_message ?? 'Payment failed.',
                'payment_reference' => $qrPayment->payment_reference,
            ]);
        }

        // Query CGrate for status
        $cgrateService = new CGrateService();
        $result = $cgrateService->queryCustomerPayment($qrPayment->payment_reference);

        if ($result['payment_complete']) {
            $qrPayment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'response_message' => $result['message'] ?? 'Payment completed',
                'response_code' => $result['responseCode'] ?? null,
            ]);

            // Auto-deduct from student balance
            $this->processPaymentDeduction($qrPayment);

            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => 'Payment completed successfully!',
                'transaction_id' => $qrPayment->cgrate_payment_id,
                'payment_reference' => $qrPayment->payment_reference,
                'amount' => $qrPayment->amount,
                'mobile_number' => $qrPayment->customer_mobile,
                'completed_at' => $qrPayment->completed_at?->format('Y-m-d H:i:s'),
            ]);
        } else {
            // Check if payment has failed/cancelled
            $paymentStatus = strtolower($result['payment_status'] ?? '');
            if (in_array($paymentStatus, ['failed', 'cancelled', 'declined', 'rejected'])) {
                $qrPayment->update([
                    'status' => 'failed',
                    'response_message' => $result['message'] ?? 'Payment failed',
                    'response_code' => $result['responseCode'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'failed',
                    'message' => $result['message'] ?? 'Payment was not successful.',
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $qrPayment->status,
                'message' => $result['message'] ?? 'Payment is still pending.',
            ]);
        }
    }

    /**
     * Process payment deduction from student balance
     */
    protected function processPaymentDeduction(QrPayment $payment)
    {
        if (!$payment->student_id || $payment->status !== 'completed') {
            return;
        }

        // Get student's unpaid fees ordered by oldest first
        $fees = StudentFee::where('student_id', $payment->student_id)
            ->where('balance', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $remainingAmount = $payment->amount;

        foreach ($fees as $fee) {
            if ($remainingAmount <= 0) {
                break;
            }

            $balanceDue = $fee->balance;
            $amountToApply = min($remainingAmount, $balanceDue);

            $newAmountPaid = $fee->amount_paid + $amountToApply;
            $newBalance = $balanceDue - $amountToApply;

            $fee->update([
                'amount_paid' => $newAmountPaid,
                'balance' => $newBalance,
                'payment_status' => $newBalance <= 0 ? 'paid' : 'partial',
            ]);

            $remainingAmount -= $amountToApply;
        }
    }

}
