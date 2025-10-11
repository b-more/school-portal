<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Fee Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .header p {
            color: #6b7280;
            font-size: 10px;
        }
        .receipt-number {
            text-align: right;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #eff6ff;
            color: #1e40af;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: bold;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .info-col h3 {
            font-size: 11px;
            color: #1e40af;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-row {
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
        }
        .info-value {
            color: #111827;
            margin-top: 2px;
        }
        .fee-breakdown {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 20px;
        }
        .fee-breakdown h3 {
            color: #1e40af;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .fee-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e5e7eb;
        }
        .fee-item:last-child {
            border-bottom: none;
        }
        .fee-label {
            color: #374151;
        }
        .fee-amount {
            font-weight: bold;
            color: #111827;
        }
        .total-row {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        .payment-summary {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .payment-box {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 2px solid #e5e7eb;
        }
        .payment-box.paid {
            background-color: #d1fae5;
            border-color: #059669;
        }
        .payment-box.balance {
            background-color: #fee2e2;
            border-color: #dc2626;
        }
        .payment-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .payment-amount {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .transactions-table th {
            background-color: #1e40af;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .transactions-table td {
            padding: 7px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .transactions-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .notice-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 10px 15px;
            margin: 20px 0;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px 10px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Fee Statement</h2>
        <p>Generated on {{ $reportDate }}</p>
    </div>

    {{-- Receipt Number --}}
    @if($fee->receipt_number)
    <div class="receipt-number">
        <strong>Receipt No:</strong> {{ $fee->receipt_number }}
    </div>
    @endif

    {{-- Student and Fee Information --}}
    <div class="info-grid">
        <div class="info-col">
            <h3>Student Information</h3>
            <div class="info-row">
                <div class="info-label">Student ID</div>
                <div class="info-value"><strong>{{ $fee->student->student_id_number }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Name</div>
                <div class="info-value">{{ $fee->student->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Grade</div>
                <div class="info-value">{{ $fee->student->grade->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Class Section</div>
                <div class="info-value">{{ $fee->student->classSection->name ?? 'N/A' }}</div>
            </div>
        </div>

        <div class="info-col">
            <h3>Fee Period</h3>
            <div class="info-row">
                <div class="info-label">Academic Year</div>
                <div class="info-value">{{ $fee->academicYear->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Term</div>
                <div class="info-value">{{ $fee->term->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Status</div>
                <div class="info-value">
                    @if($fee->payment_status === 'paid')
                        <span class="badge badge-success">Fully Paid</span>
                    @elseif($fee->payment_status === 'partial')
                        <span class="badge badge-warning">Partially Paid</span>
                    @else
                        <span class="badge badge-danger">Unpaid</span>
                    @endif
                </div>
            </div>
            @if($fee->payment_date)
            <div class="info-row">
                <div class="info-label">Last Payment Date</div>
                <div class="info-value">{{ $fee->payment_date->format('F d, Y') }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Fee Breakdown --}}
    <div class="fee-breakdown">
        <h3>Fee Breakdown</h3>

        <div class="fee-item">
            <span class="fee-label">Basic Fee (Tuition)</span>
            <span class="fee-amount">ZMW {{ number_format($fee->feeStructure->basic_fee, 2) }}</span>
        </div>

        @if($fee->feeStructure->additional_charges && count($fee->feeStructure->additional_charges) > 0)
            @foreach($fee->feeStructure->additional_charges as $charge)
                <div class="fee-item">
                    <span class="fee-label">{{ $charge['description'] ?? 'Additional Charge' }}</span>
                    <span class="fee-amount">ZMW {{ number_format($charge['amount'] ?? 0, 2) }}</span>
                </div>
            @endforeach
        @endif

        <div class="total-row">
            <div style="display: flex; justify-content: space-between;">
                <span>TOTAL FEE</span>
                <span>ZMW {{ number_format($fee->feeStructure->total_fee, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Payment Summary --}}
    <div class="payment-summary">
        <div class="payment-box">
            <div class="payment-label">Total Fee</div>
            <div class="payment-amount">ZMW {{ number_format($fee->feeStructure->total_fee, 2) }}</div>
        </div>
        <div class="payment-box paid">
            <div class="payment-label">Amount Paid</div>
            <div class="payment-amount" style="color: #059669;">ZMW {{ number_format($fee->amount_paid, 2) }}</div>
        </div>
        <div class="payment-box balance">
            <div class="payment-label">Balance Due</div>
            <div class="payment-amount" style="color: #dc2626;">ZMW {{ number_format($fee->balance, 2) }}</div>
        </div>
    </div>

    {{-- Payment Transactions --}}
    @if($fee->paymentTransactions && $fee->paymentTransactions->count() > 0)
    <div class="section">
        <div class="section-title">Payment History</div>
        <table class="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fee->paymentTransactions->sortBy('transaction_date') as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($transaction->type) }}</td>
                    <td><strong>ZMW {{ number_format($transaction->amount, 2) }}</strong></td>
                    <td>{{ $transaction->payment_method ?? 'N/A' }}</td>
                    <td>{{ $transaction->reference_number ?? '-' }}</td>
                    <td>{{ $transaction->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Notice for Outstanding Balance --}}
    @if($fee->balance > 0)
    <div class="notice-box">
        <strong>NOTICE:</strong> An outstanding balance of <strong>ZMW {{ number_format($fee->balance, 2) }}</strong> is due.
        Please make payment at your earliest convenience to avoid any disruption to educational services.
    </div>
    @endif

    {{-- Parent/Guardian Information --}}
    @if($fee->student->parentGuardian)
    <div class="section">
        <div class="section-title">Parent/Guardian Information</div>
        <div style="padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
            <div style="margin-bottom: 5px;">
                <strong>Name:</strong> {{ $fee->student->parentGuardian->name }}
            </div>
            <div style="margin-bottom: 5px;">
                <strong>Phone:</strong> {{ $fee->student->parentGuardian->phone }}
            </div>
            @if($fee->student->parentGuardian->email)
            <div>
                <strong>Email:</strong> {{ $fee->student->parentGuardian->email }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Additional Notes --}}
    @if($fee->notes)
    <div class="section">
        <div class="section-title">Additional Notes</div>
        <p style="padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
            {{ $fee->notes }}
        </p>
    </div>
    @endif

    {{-- Signature Section --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                Prepared By<br>
                <em style="font-size: 9px;">Accounts Office</em>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                Received By<br>
                <em style="font-size: 9px;">Parent/Guardian Signature</em>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>{{ $schoolName }}</strong></p>
        <p>Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
        <p>Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        <p style="margin-top: 10px;">Generated on {{ $reportDate }} | This is a computer-generated document.</p>
    </div>
</body>
</html>
