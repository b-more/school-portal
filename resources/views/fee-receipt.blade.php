<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.5;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
            box-sizing: border-box;
            max-height: 50vh; /* Half of viewport height */
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #4a7fb5;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 70px;
            margin-bottom: 5px;
        }
        .title {
            font-size: 20px;
            color: #003366;
            margin: 5px 0;
            font-weight: bold;
        }
        .subtitle {
            font-size: 16px;
            color: #4a7fb5;
            margin: 5px 0;
        }
        .receipt-info {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        .receipt-no {
            font-weight: bold;
            font-size: 14px;
        }
        .date {
            text-align: right;
        }
        .student-info, .payment-info {
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 5px;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 100px;
        }
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .payment-table th {
            background-color: #4a7fb5;
            color: white;
            font-size: 12px;
        }
        .amount-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 15px;
            padding-top: 3px;
            font-size: 11px;
        }
        .receipt-copy {
            position: absolute;
            top: 40%;
            left: 20%;
            transform: rotate(-45deg);
            font-size: 40px;
            color: rgba(200, 200, 200, 0.2);
            font-weight: bold;
            z-index: -1;
        }
        .watermark {
            position: fixed;
            top: 25%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            z-index: -1;
        }
        .contact-info {
            font-size: 10px;
            margin: 2px 0;
        }
        p {
            margin: 3px 0;
        }
        .notes {
            font-size: 11px;
            font-style: italic;
            margin-bottom: 10px;
        }
        .error-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" width="150">
            @endif
        </div>

        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            @endif
            <h1 class="title">St. Francis Of Assisi Private School</h1>
            <p class="subtitle">Official Payment Receipt</p>
            <p class="contact-info">Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
            <p class="contact-info">Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        </div>

        <div class="receipt-info">
            <div class="receipt-no">Receipt No: {{ $studentFee->receipt_number ?? 'N/A' }}</div>
            <div class="date">Date: {{ $studentFee->payment_date ? $studentFee->payment_date->format('F j, Y') : now()->format('F j, Y') }}</div>
        </div>

        @if(!$studentFee->feeStructure)
            <div class="error-notice">
                <strong>Notice:</strong> Fee structure information is not available for this receipt.
                Please contact the school office for complete fee details.
            </div>
        @endif

        <div class="student-info">
            <table class="info-table">
                <tr>
                    <td>Student:</td>
                    <td>{{ $studentFee->student->name ?? 'Unknown Student' }}</td>
                    <td>ID:</td>
                    <td>{{ $studentFee->student->student_id_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Grade:</td>
                    <td>
                        @if($studentFee->feeStructure && $studentFee->feeStructure->grade)
                            {{ $studentFee->feeStructure->grade->name }}
                        @elseif($studentFee->student && $studentFee->student->grade)
                            {{ $studentFee->student->grade->name }}
                        @elseif($studentFee->grade)
                            {{ $studentFee->grade->name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>Term:</td>
                    <td>
                        @if($studentFee->feeStructure && $studentFee->feeStructure->term)
                            {{ $studentFee->feeStructure->term->name }}
                        @elseif($studentFee->term)
                            {{ $studentFee->term->name }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Year:</td>
                    <td colspan="3">
                        @if($studentFee->feeStructure && $studentFee->feeStructure->academicYear)
                            {{ $studentFee->feeStructure->academicYear->name }}
                        @elseif($studentFee->academicYear)
                            {{ $studentFee->academicYear->name }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="payment-info">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th width="60%">Description</th>
                        <th width="40%">Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalFee = 0;
                        $thisPayment = $lastPaymentAmount ?? $studentFee->amount_paid ?? 0;
                        $totalPaid = $studentFee->amount_paid ?? 0;
                        $previouslyPaid = $totalPaid - $thisPayment;
                        $balance = $studentFee->balance ?? 0;

                        // Try to get total fee from various sources
                        if ($studentFee->feeStructure && $studentFee->feeStructure->total_fee) {
                            $totalFee = $studentFee->feeStructure->total_fee;
                        } else {
                            // Calculate from payment data if fee structure is missing
                            $totalFee = $totalPaid + $balance;
                        }
                    @endphp

                    <tr>
                        <td>Total Term Fee</td>
                        <td>{{ number_format($totalFee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Previously Paid</td>
                        <td>{{ number_format(max(0, $previouslyPaid), 2) }}</td>
                    </tr>
                    <tr class="amount-row">
                        <td>This Payment</td>
                        <td>{{ number_format($thisPayment, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Outstanding Balance</strong></td>
                        <td><strong>{{ number_format($balance, 2) }}</strong></td>
                    </tr>
                    @if($studentFee->payment_method)
                    <tr>
                        <td>Payment Method</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $studentFee->payment_method)) }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($studentFee->notes)
            <div class="notes">
                <strong>Notes:</strong> {{ $studentFee->notes }}
            </div>
        @endif

        <div class="signature-section">
            <div class="signature">
                <div class="signature-line">Parent/Guardian</div>
            </div>
            <div class="signature">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="footer">
            @if($studentFee->payment_status === 'paid')
                <p><strong>✓ FULLY PAID</strong> - Thank you for your payment!</p>
            @elseif($studentFee->payment_status === 'partial')
                <p><strong>PARTIALLY PAID</strong> - Balance: ZMW {{ number_format($balance, 2) }}</p>
            @else
                <p><strong>PAYMENT RECEIVED</strong> - Status: {{ ucfirst($studentFee->payment_status ?? 'Unknown') }}</p>
            @endif
            <p>This is an official receipt of St. Francis Of Assisi Private School.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
