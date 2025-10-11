<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Unpaid Fees Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
            box-sizing: border-box;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 12px;
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
            color: #dc3545;
            margin: 5px 0;
            font-weight: bold;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .report-meta {
            font-size: 10px;
        }
        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
        }
        .summary-card {
            flex: 1;
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 5px;
            padding: 8px;
            text-align: center;
        }
        .summary-card.danger {
            background-color: #f8d7da;
            border-color: #dc3545;
        }
        .summary-card.info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
        }
        .card-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .card-amount {
            font-size: 16px;
            font-weight: bold;
            color: #003366;
        }
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .fees-table th,
        .fees-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: left;
        }
        .fees-table th {
            background-color: #dc3545;
            color: white;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .fees-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .fees-table tbody tr:hover {
            background-color: #fff3cd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .totals-row {
            background-color: #343a40 !important;
            color: white;
            font-weight: bold;
        }
        .totals-row td {
            border-top: 3px solid #000;
            padding: 8px 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-partial {
            background-color: #ffc107;
            color: #333;
        }
        .status-unpaid {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            z-index: -1;
            font-size: 80px;
            font-weight: bold;
            color: #dc3545;
        }
        .contact-info {
            font-size: 9px;
            margin: 2px 0;
        }
        .highlight-balance {
            color: #dc3545;
            font-weight: bold;
        }
        .confidential {
            color: #dc3545;
            font-weight: bold;
            font-size: 10px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">CONFIDENTIAL</div>

        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            @endif
            <h1 class="title">St. Francis Of Assisi Private School</h1>
            <p class="subtitle">UNPAID FEES REPORT</p>
            <p class="contact-info">Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
            <p class="contact-info">Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        </div>

        <div class="report-info">
            <div>
                <p class="report-meta"><strong>Report Generated:</strong> {{ $generatedDate->format('d M Y H:i') }}</p>
                <p class="report-meta"><strong>Academic Year:</strong> {{ $currentYear->name ?? 'N/A' }}</p>
                <p class="report-meta"><strong>Term:</strong> {{ $currentTerm->name ?? 'All Terms' }}</p>
            </div>
            <div style="text-align: right;">
                <p class="report-meta"><strong>Total Students:</strong> {{ $unpaidFees->count() }}</p>
                <p class="report-meta"><strong>Report Type:</strong> Outstanding Balances</p>
                <p class="confidential">CONFIDENTIAL DOCUMENT</p>
            </div>
        </div>

        <div class="summary-cards">
            <div class="summary-card info">
                <div class="card-label">Total Expected Fees</div>
                <div class="card-amount">ZMW {{ number_format($totalFees, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="card-label">Total Collected</div>
                <div class="card-amount">ZMW {{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="summary-card danger">
                <div class="card-label">Outstanding Balance</div>
                <div class="card-amount">ZMW {{ number_format($totalBalance, 2) }}</div>
            </div>
        </div>

        <h3 style="color: #dc3545; margin: 15px 0 8px 0; font-size: 13px;">Students with Outstanding Balances</h3>

        <table class="fees-table">
            <thead>
                <tr>
                    <th width="3%" class="text-center">#</th>
                    <th width="15%">Student Name</th>
                    <th width="8%">Student ID</th>
                    <th width="8%">Grade</th>
                    <th width="8%">Term</th>
                    <th width="8%" class="text-right">Total Fee</th>
                    <th width="8%" class="text-right">Paid</th>
                    <th width="10%" class="text-right">Balance</th>
                    <th width="8%">Status</th>
                    <th width="12%">Parent/Guardian</th>
                    <th width="10%">Phone</th>
                </tr>
            </thead>
            <tbody>
                @foreach($unpaidFees as $index => $fee)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>{{ $fee->student->name ?? 'N/A' }}</td>
                    <td>{{ $fee->student->student_id_number ?? 'N/A' }}</td>
                    <td>{{ $fee->feeStructure->grade->name ?? 'N/A' }}</td>
                    <td>{{ $fee->feeStructure->term->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($fee->feeStructure->total_fee ?? 0, 2) }}</td>
                    <td class="text-right">{{ number_format($fee->amount_paid ?? 0, 2) }}</td>
                    <td class="text-right highlight-balance">{{ number_format($fee->balance ?? 0, 2) }}</td>
                    <td>
                        <span class="status-badge status-{{ $fee->payment_status }}">
                            {{ $fee->payment_status === 'partial' ? 'Partial' : 'Unpaid' }}
                        </span>
                    </td>
                    <td style="font-size: 9px;">{{ $fee->student->parentGuardian->name ?? 'N/A' }}</td>
                    <td style="font-size: 9px;">{{ $fee->student->parentGuardian->phone ?? 'N/A' }}</td>
                </tr>
                @endforeach

                <tr class="totals-row">
                    <td colspan="5" class="text-right"><strong>TOTALS:</strong></td>
                    <td class="text-right">{{ number_format($totalFees, 2) }}</td>
                    <td class="text-right">{{ number_format($totalPaid, 2) }}</td>
                    <td class="text-right">{{ number_format($totalBalance, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 15px; border-radius: 3px;">
            <p style="margin: 3px 0; font-size: 10px;"><strong>Summary:</strong></p>
            <p style="margin: 3px 0; font-size: 10px;">- Total Students with Outstanding Balances: <strong>{{ $unpaidFees->count() }}</strong></p>
            <p style="margin: 3px 0; font-size: 10px;">- Total Outstanding Amount: <strong class="highlight-balance">ZMW {{ number_format($totalBalance, 2) }}</strong></p>
            <p style="margin: 3px 0; font-size: 10px;">- Collection Rate: <strong>{{ $totalFees > 0 ? number_format(($totalPaid / $totalFees) * 100, 1) : 0 }}%</strong></p>
        </div>

        <div class="footer">
            <p class="confidential">*** CONFIDENTIAL - FOR INTERNAL USE ONLY ***</p>
            <p><strong>This document contains sensitive financial information and should be handled accordingly.</strong></p>
            <p>Generated by St. Francis Of Assisi Private School Financial Management System</p>
            <p style="margin-top: 8px;">(c) {{ date('Y') }} St. Francis Of Assisi Private School. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
