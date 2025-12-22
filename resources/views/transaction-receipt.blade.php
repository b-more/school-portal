<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaction Receipt</title>
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
            background-color: #e8f4f8;
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
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
        }
        .transaction-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
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
            <p class="subtitle">Individual Transaction Receipt</p>
            <p class="contact-info">Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
            <p class="contact-info">Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        </div>

        <div class="receipt-info">
            <div class="receipt-no">
                Receipt No: {{ $transaction->reference_number ?? 'N/A' }}
                <span class="transaction-badge">TRANSACTION</span>
            </div>
            <div class="date">Date: {{ $transaction->transaction_date ? $transaction->transaction_date->format('F j, Y') : now()->format('F j, Y') }}</div>
        </div>

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
                    <tr>
                        <td>Total Term Fee</td>
                        <td>{{ number_format($totalFee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Previously Paid (Before This Transaction)</td>
                        <td>{{ number_format($previouslyPaid, 2) }}</td>
                    </tr>
                    <tr class="amount-row">
                        <td><strong>This Transaction Amount</strong></td>
                        <td><strong>{{ number_format($transaction->amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Balance After This Transaction</td>
                        <td>{{ number_format($runningBalance, 2) }}</td>
                    </tr>
                    @if($transaction->payment_method)
                    <tr>
                        <td>Payment Method</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</td>
                    </tr>
                    @endif
                    @if($transaction->reference_number)
                    <tr>
                        <td>Transaction Reference</td>
                        <td>{{ $transaction->reference_number }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if($transaction->notes)
            <div class="notes">
                <strong>Transaction Notes:</strong> {{ $transaction->notes }}
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
            <p><strong>✓ PAYMENT RECEIVED</strong> - Amount: ZMW {{ number_format($transaction->amount, 2) }}</p>
            <p>This is an individual transaction receipt for the specific payment listed above.</p>
            <p>For complete payment history, please request a full statement.</p>
            <p>This is an official receipt of St. Francis Of Assisi Private School.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
