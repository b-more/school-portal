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
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            font-size: 14px;
            color: #4a7fb5;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #4a7fb5;
            color: white;
        }
        .summary-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .summary-table .total-row {
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
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        .col {
            flex: 1;
            padding: 0 10px;
            min-width: 48%;
        }
        .money {
            text-align: right;
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
            <p><strong>Academic Year:</strong> {{ $reportData['academicYear']->name ?? 'N/A' }}</p>
            @if(isset($reportData['term']))
                <p><strong>Term:</strong> {{ $reportData['term']->name }}</p>
            @endif
        </div>

        <div class="summary-box">
            <div class="summary-title">Overall Fee Collection Summary</div>
            <div class="row">
                <div class="col">
                    <p><strong>Total Students:</strong> {{ $reportData['totalStats']['count'] }}</p>
                    <p><strong>Total Expected Fees:</strong> ZMW {{ number_format($reportData['totalStats']['total_fees'], 2) }}</p>
                    <p><strong>Total Amount Collected:</strong> ZMW {{ number_format($reportData['totalStats']['total_paid'], 2) }}</p>
                </div>
                <div class="col">
                    <p><strong>Outstanding Balance:</strong> ZMW {{ number_format($reportData['totalStats']['total_balance'], 2) }}</p>
                    <p><strong>Collection Rate:</strong>
                        @if($reportData['totalStats']['total_fees'] > 0)
                            {{ number_format(($reportData['totalStats']['total_paid'] / $reportData['totalStats']['total_fees']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="summary-box">
                    <div class="summary-title">Payment Status</div>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Amount Paid</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalStudents = $reportData['totalStats']['count'] ?? 0;
                            @endphp
                            @foreach($reportData['statusStats'] as $status)
                            <tr>
                                <td>
                                    @if($status->payment_status == 'paid')
                                        <span class="status-paid">Fully Paid</span>
                                    @elseif($status->payment_status == 'partial')
                                        <span class="status-partial">Partially Paid</span>
                                    @else
                                        <span class="status-unpaid">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ $status->count }}</td>
                                <td>
                                    @if($totalStudents > 0)
                                        {{ number_format(($status->count / $totalStudents) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                                <td class="money">{{ number_format($status->total_paid, 2) }}</td>
                                <td class="money">{{ number_format($status->total_balance, 2) }}</td>
                            </tr>
                            @endforeach
                            <tr class="total-row">
                                <td><strong>Total</strong></td>
                                <td>{{ $totalStudents }}</td>
                                <td>100%</td>
                                <td class="money">{{ number_format($reportData['totalStats']['total_paid'], 2) }}</td>
                                <td class="money">{{ number_format($reportData['totalStats']['total_balance'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col">
                <div class="summary-box">
                    <div class="summary-title">Payment Methods</div>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th>Count</th>
                                <th>Amount</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalPaid = $reportData['totalStats']['total_paid'] ?? 0;
                            @endphp
                            @foreach($reportData['paymentMethodStats'] as $method)
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $method->payment_method)) }}</td>
                                <td>{{ $method->count }}</td>
                                <td class="money">{{ number_format($method->total_paid, 2) }}</td>
                                <td>
                                    @if($totalPaid > 0)
                                        {{ number_format(($method->total_paid / $totalPaid) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr class="total-row">
                                <td><strong>Total</strong></td>
                                <td>{{ $reportData['paymentMethodStats']->sum('count') }}</td>
                                <td class="money">{{ number_format($totalPaid, 2) }}</td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-title">Grade-Level Analysis</div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Student Count</th>
                        <th>Total Paid</th>
                        <th>Total Balance</th>
                        <th>Collection %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalCollected = $reportData['totalStats']['total_paid'] ?? 0;
                    @endphp
                    @foreach($reportData['gradeStats'] as $grade)
                    <tr>
                        <td>{{ $grade->grade_name }}</td>
                        <td>{{ $grade->count }}</td>
                        <td class="money">{{ number_format($grade->total_paid, 2) }}</td>
                        <td class="money">{{ number_format($grade->total_balance, 2) }}</td>
                        <td>
                            @php
                                $total = $grade->total_paid + $grade->total_balance;
                            @endphp
                            @if($total > 0)
                                {{ number_format(($grade->total_paid / $total) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><strong>Total</strong></td>
                        <td>{{ $reportData['gradeStats']->sum('count') }}</td>
                        <td class="money">{{ number_format($reportData['gradeStats']->sum('total_paid'), 2) }}</td>
                        <td class="money">{{ number_format($reportData['gradeStats']->sum('total_balance'), 2) }}</td>
                        <td>
                            @php
                                $grandTotal = $reportData['gradeStats']->sum('total_paid') + $reportData['gradeStats']->sum('total_balance');
                            @endphp
                            @if($grandTotal > 0)
                                {{ number_format(($reportData['gradeStats']->sum('total_paid') / $grandTotal) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
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
            <p>This is an official fee summary report of St. Francis Of Assisi Private School.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
