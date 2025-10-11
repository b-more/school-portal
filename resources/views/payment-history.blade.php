<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Complete Payment History</title>
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
            padding: 20px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4a7fb5;
            padding-bottom: 15px;
        }
        .logo {
            max-width: 80px;
            margin-bottom: 5px;
        }
        .title {
            font-size: 22px;
            color: #003366;
            margin: 5px 0;
            font-weight: bold;
        }
        .subtitle {
            font-size: 18px;
            color: #4a7fb5;
            margin: 5px 0;
        }
        .document-info {
            margin-bottom: 20px;
            text-align: right;
        }
        .document-date {
            font-size: 11px;
            color: #666;
        }
        .student-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
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
            width: 120px;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        .summary-card {
            flex: 1;
            background-color: #e8f4f8;
            border: 1px solid #4a7fb5;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .summary-card.paid {
            background-color: #d4edda;
            border-color: #28a745;
        }
        .summary-card.balance {
            background-color: #fff3cd;
            border-color: #ffc107;
        }
        .card-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .card-amount {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .transactions-table th,
        .transactions-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .transactions-table th {
            background-color: #4a7fb5;
            color: white;
            font-size: 11px;
            text-transform: uppercase;
        }
        .transactions-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .transactions-table tbody tr:hover {
            background-color: #e8f4f8;
        }
        .amount-cell {
            text-align: right;
            font-weight: bold;
        }
        .balance-cell {
            text-align: right;
            color: #dc3545;
            font-weight: bold;
        }
        .totals-row {
            background-color: #4a7fb5 !important;
            color: white;
            font-weight: bold;
        }
        .totals-row td {
            border-top: 2px solid #003366;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.05;
            z-index: -1;
        }
        .contact-info {
            font-size: 10px;
            margin: 2px 0;
        }
        p {
            margin: 3px 0;
        }
        .payment-status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #28a745;
            color: white;
        }
        .status-partial {
            background-color: #ffc107;
            color: #333;
        }
        .status-unpaid {
            background-color: #dc3545;
            color: white;
        }
        .transaction-number {
            color: #4a7fb5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" width="200">
            @endif
        </div>

        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            @endif
            <h1 class="title">St. Francis Of Assisi Private School</h1>
            <p class="subtitle">Complete Payment History</p>
            <p class="contact-info">Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
            <p class="contact-info">Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        </div>

        <div class="document-info">
            <p class="document-date">Generated: {{ now()->format('F j, Y g:i A') }}</p>
        </div>

        <div class="student-info">
            <table class="info-table">
                <tr>
                    <td>Student Name:</td>
                    <td>{{ $studentFee->student->name ?? 'Unknown Student' }}</td>
                    <td>Student ID:</td>
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
                    <td>Academic Year:</td>
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

        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-label">Total Fee</div>
                <div class="card-amount">ZMW {{ number_format($totalFee, 2) }}</div>
            </div>
            <div class="summary-card paid">
                <div class="card-label">Total Paid</div>
                <div class="card-amount">ZMW {{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="summary-card balance">
                <div class="card-label">Outstanding Balance</div>
                <div class="card-amount">ZMW {{ number_format($balance, 2) }}</div>
            </div>
        </div>

        @if($transactions->isNotEmpty())
            <h3 style="color: #003366; margin-bottom: 10px;">Payment Transaction History</h3>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Date</th>
                        <th width="20%">Reference</th>
                        <th width="15%">Payment Method</th>
                        <th width="15%">Amount (ZMW)</th>
                        <th width="15%">Balance (ZMW)</th>
                        <th width="15%">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $runningBalance = $totalFee;
                    @endphp
                    @foreach($transactions as $index => $transaction)
                        @php
                            $runningBalance -= $transaction->amount;
                        @endphp
                        <tr>
                            <td class="transaction-number">{{ $index + 1 }}</td>
                            <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                            <td>{{ $transaction->reference_number ?? 'N/A' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->payment_method ?? 'N/A')) }}</td>
                            <td class="amount-cell">{{ number_format($transaction->amount, 2) }}</td>
                            <td class="balance-cell">{{ number_format(max(0, $runningBalance), 2) }}</td>
                            <td style="font-size: 10px;">{{ $transaction->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="totals-row">
                        <td colspan="4" style="text-align: right;">TOTALS:</td>
                        <td class="amount-cell">{{ number_format($totalPaid, 2) }}</td>
                        <td class="balance-cell">{{ number_format($balance, 2) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @else
            <div style="text-align: center; padding: 40px; background-color: #f8f9fa; border-radius: 5px; margin: 20px 0;">
                <p style="font-size: 14px; color: #666;">No payment transactions found for this fee record.</p>
            </div>
        @endif

        <div style="margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #4a7fb5; border-radius: 3px;">
            <p style="margin: 5px 0; font-weight: bold;">Payment Status:
                @if($balance <= 0)
                    <span class="payment-status-badge status-paid">Fully Paid</span>
                @elseif($totalPaid > 0)
                    <span class="payment-status-badge status-partial">Partially Paid</span>
                @else
                    <span class="payment-status-badge status-unpaid">Unpaid</span>
                @endif
            </p>
            @if($balance > 0)
                <p style="margin: 5px 0; color: #856404;">Remaining balance to be paid: <strong>ZMW {{ number_format($balance, 2) }}</strong></p>
            @else
                <p style="margin: 5px 0; color: #28a745;">All fees have been paid in full. Thank you!</p>
            @endif
        </div>

        <div class="footer">
            <p><strong>This is an official document of St. Francis Of Assisi Private School.</strong></p>
            <p>This document provides a complete chronological history of all payment transactions.</p>
            <p>For individual transaction receipts, please contact the school office.</p>
            <p style="margin-top: 10px;">© {{ date('Y') }} St. Francis Of Assisi Private School. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
