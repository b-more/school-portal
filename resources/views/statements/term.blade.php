<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportData['title'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4a7fb5;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 80px;
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
        .info-section {
            margin-bottom: 20px;
        }
        .summary-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .grade-table th {
            background-color: #4a7fb5;
            color: white;
        }
        .grade-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .grade-table .total-row {
            font-weight: bold;
            background-color: #e6f2ff !important;
        }
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        .fee-table th, .fee-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .fee-table th {
            background-color: #4a7fb5;
            color: white;
        }
        .fee-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .fee-table .total-row {
            font-weight: bold;
            background-color: #e6f2ff !important;
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
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            opacity: 0.05;
            z-index: -1;
        }
        .contact-info {
            font-size: 10px;
            margin: 2px 0;
        }
        .grade-heading {
            background-color: #e6f2ff;
            padding: 5px;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            border-left: 4px solid #4a7fb5;
        }
        .status-paid {
            color: green;
            font-weight: bold;
        }
        .status-partial {
            color: orange;
            font-weight: bold;
        }
        .status-unpaid {
            color: red;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
        .page-break {
            page-break-after: always;
        }
        .chart-container {
            width: 100%;
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="watermark">
            St. Francis
        </div>

        <div class="header">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            @endif
            <h1 class="title">St. Francis Of Assisi Private School</h1>
            <p class="subtitle">{{ $reportData['title'] }}</p>
            <p class="contact-info">Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
            <p class="contact-info">Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        </div>

        <div class="info-section">
            <p><strong>Date Generated:</strong> {{ $reportData['date'] }}</p>
            <p><strong>Academic Year:</strong> {{ $reportData['academicYear'] }}</p>
            <p><strong>Term:</strong> {{ $reportData['term']->name }}</p>
            <p><strong>Term Period:</strong>
                {{ $reportData['term']->start_date ? date('M d, Y', strtotime($reportData['term']->start_date)) : 'N/A' }} -
                {{ $reportData['term']->end_date ? date('M d, Y', strtotime($reportData['term']->end_date)) : 'N/A' }}
            </p>
        </div>

        <div class="summary-box">
            <div class="summary-title">Term Payment Summary</div>
            <p><strong>Total Students:</strong> {{ $reportData['count'] }}</p>
            <p><strong>Total Fees:</strong> ZMW {{ number_format($reportData['totalFees'], 2) }}</p>
            <p><strong>Total Paid:</strong> ZMW {{ number_format($reportData['totalPaid'], 2) }}</p>
            <p><strong>Total Balance:</strong> ZMW {{ number_format($reportData['totalBalance'], 2) }}</p>
            <p><strong>Collection Rate:</strong>
                @if($reportData['totalFees'] > 0)
                    {{ number_format(($reportData['totalPaid'] / $reportData['totalFees']) * 100, 1) }}%
                @else
                    0%
                @endif
            </p>
        </div>

        <div class="grade-summary">
            <h3>Payment Summary by Grade</h3>
            @if($studentFees->isEmpty())
                <p>No fee records found for this term.</p>
            @else
                @php
                    // Group student fees by grade
                    $feesByGrade = $studentFees->groupBy(function($fee) {
                        return $fee->student->grade_id ? $fee->student->grade->name : ($fee->student->grade ?? 'Unknown');
                    });

                    // Calculate statistics for each grade
                    $gradeStats = [];
                    foreach($feesByGrade as $gradeName => $gradeFees) {
                        $gradeStats[$gradeName] = [
                            'count' => $gradeFees->count(),
                            'totalFees' => $gradeFees->sum(function($fee) { return $fee->feeStructure->total_fee; }),
                            'totalPaid' => $gradeFees->sum('amount_paid'),
                            'totalBalance' => $gradeFees->sum('balance'),
                            'paidCount' => $gradeFees->where('payment_status', 'paid')->count(),
                            'partialCount' => $gradeFees->where('payment_status', 'partial')->count(),
                            'unpaidCount' => $gradeFees->where('payment_status', 'unpaid')->count(),
                        ];
                    }
                @endphp

                <table class="grade-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Total Students</th>
                            <th>Total Fees</th>
                            <th>Total Paid</th>
                            <th>Total Balance</th>
                            <th>Collection Rate</th>
                            <th>Fully Paid</th>
                            <th>Partial</th>
                            <th>Unpaid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gradeStats as $gradeName => $stats)
                        <tr>
                            <td>{{ $gradeName }}</td>
                            <td>{{ $stats['count'] }}</td>
                            <td>{{ number_format($stats['totalFees'], 2) }}</td>
                            <td>{{ number_format($stats['totalPaid'], 2) }}</td>
                            <td>{{ number_format($stats['totalBalance'], 2) }}</td>
                            <td>
                                @if($stats['totalFees'] > 0)
                                    {{ number_format(($stats['totalPaid'] / $stats['totalFees']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </td>
                            <td>{{ $stats['paidCount'] }} ({{ number_format(($stats['paidCount'] / $stats['count']) * 100, 1) }}%)</td>
                            <td>{{ $stats['partialCount'] }} ({{ number_format(($stats['partialCount'] / $stats['count']) * 100, 1) }}%)</td>
                            <td>{{ $stats['unpaidCount'] }} ({{ number_format(($stats['unpaidCount'] / $stats['count']) * 100, 1) }}%)</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td>{{ $reportData['count'] }}</td>
                            <td>{{ number_format($reportData['totalFees'], 2) }}</td>
                            <td>{{ number_format($reportData['totalPaid'], 2) }}</td>
                            <td>{{ number_format($reportData['totalBalance'], 2) }}</td>
                            <td>
                                @if($reportData['totalFees'] > 0)
                                    {{ number_format(($reportData['totalPaid'] / $reportData['totalFees']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </td>
                            <td>{{ $studentFees->where('payment_status', 'paid')->count() }} ({{ number_format(($studentFees->where('payment_status', 'paid')->count() / $reportData['count']) * 100, 1) }}%)</td>
                            <td>{{ $studentFees->where('payment_status', 'partial')->count() }} ({{ number_format(($studentFees->where('payment_status', 'partial')->count() / $reportData['count']) * 100, 1) }}%)</td>
                            <td>{{ $studentFees->where('payment_status', 'unpaid')->count() }} ({{ number_format(($studentFees->where('payment_status', 'unpaid')->count() / $reportData['count']) * 100, 1) }}%)</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        <div class="student-details">
            <h3>Detailed Fee Payment Records</h3>
            @if($studentFees->isEmpty())
                <p>No fee records found for this term.</p>
            @else
                @foreach($feesByGrade as $gradeName => $gradeFees)
                    <div class="grade-heading">{{ $gradeName }} ({{ $gradeFees->count() }} students)</div>
                    <table class="fee-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Student ID</th>
                                <th>Fee Amount</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Last Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $count = 1; @endphp
                            @foreach($gradeFees as $fee)
                            <tr>
                                <td>{{ $count++ }}</td>
                                <td>{{ $fee->student->name }}</td>
                                <td>{{ $fee->student->student_id_number ?? 'N/A' }}</td>
                                <td>{{ number_format($fee->feeStructure->total_fee, 2) }}</td>
                                <td>{{ number_format($fee->amount_paid, 2) }}</td>
                                <td>{{ number_format($fee->balance, 2) }}</td>
                                <td>
                                    @if($fee->payment_status == 'paid')
                                        <span class="status-paid">Paid</span>
                                    @elseif($fee->payment_status == 'partial')
                                        <span class="status-partial">Partial</span>
                                    @else
                                        <span class="status-unpaid">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
        </div>

        <div class="signature-section">
            <div class="signature">
                <div class="signature-line">Accountant's Signature</div>
            </div>
            <div class="signature">
                <div class="signature-line">School Stamp</div>
            </div>
        </div>

        <div class="footer">
            <p>This is an official statement of St. Francis Of Assisi Private School.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
