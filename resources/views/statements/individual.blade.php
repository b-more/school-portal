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
        .student-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .student-info td {
            padding: 5px;
        }
        .student-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .fee-table th, .fee-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .fee-table th {
            background-color: #4a7fb5;
            color: white;
            font-size: 12px;
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
        .payment-summary {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .payment-summary table {
            width: 50%;
            border-collapse: collapse;
        }
        .payment-summary td {
            padding: 5px;
        }
        .payment-summary td:first-child {
            font-weight: bold;
            width: 150px;
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
            @if(isset($reportData['term']))
                <p><strong>Term:</strong> {{ $reportData['term']->name }}</p>
            @endif
        </div>

        <div class="summary-box">
            <div class="summary-title">Student Information</div>
            <table class="student-info">
                <tr>
                    <td>Name:</td>
                    <td>{{ $reportData['student']->name }}</td>
                </tr>
                <tr>
                    <td>Student ID:</td>
                    <td>{{ $reportData['student']->student_id_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Grade:</td>
                    <td>
                        @if(isset($reportData['student']->grade_id) && $reportData['student']->grade)
                            {{ $reportData['student']->grade->name }}
                        @else
                            {{ $reportData['student']->grade ?? 'N/A' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Parent/Guardian:</td>
                    <td>
                        @if($reportData['student']->parentGuardian)
                            {{ $reportData['student']->parentGuardian->name }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Contact:</td>
                    <td>
                        @if($reportData['student']->parentGuardian)
                            {{ $reportData['student']->parentGuardian->phone }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="fee-details">
            <h3>Fee Payment Details</h3>
            @if($studentFees->isEmpty())
                <p>No fee records found for this student.</p>
            @else
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Fee Amount</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Last Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentFees as $fee)
                        <tr>
                            <td>
                                @if(isset($fee->feeStructure->term))
                                    @if(is_object($fee->feeStructure->term))
                                        {{ $fee->feeStructure->term->name }}
                                    @else
                                        {{ $fee->feeStructure->term }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>ZMW {{ number_format($fee->feeStructure->total_fee, 2) }}</td>
                            <td>ZMW {{ number_format($fee->amount_paid, 2) }}</td>
                            <td>ZMW {{ number_format($fee->balance, 2) }}</td>
                            <td>
                                @if($fee->payment_status == 'paid')
                                    <strong style="color: green;">Paid</strong>
                                @elseif($fee->payment_status == 'partial')
                                    <strong style="color: orange;">Partial</strong>
                                @else
                                    <strong style="color: red;">Unpaid</strong>
                                @endif
                            </td>
                            <td>{{ $fee->payment_date ? $fee->payment_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total</strong></td>
                            <td>ZMW {{ number_format($reportData['totalFees'], 2) }}</td>
                            <td>ZMW {{ number_format($reportData['totalPaid'], 2) }}</td>
                            <td>ZMW {{ number_format($reportData['totalBalance'], 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>

        <div class="payment-summary">
            <h3>Payment Summary</h3>
            <table>
                <tr>
                    <td>Total Fees:</td>
                    <td>ZMW {{ number_format($reportData['totalFees'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Paid:</td>
                    <td>ZMW {{ number_format($reportData['totalPaid'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Balance:</td>
                    <td>ZMW {{ number_format($reportData['totalBalance'], 2) }}</td>
                </tr>
                <tr>
                    <td>Payment Status:</td>
                    <td>
                        @if($reportData['totalBalance'] <= 0)
                            <strong style="color: green;">Fully Paid</strong>
                        @elseif($reportData['totalPaid'] > 0)
                            <strong style="color: orange;">Partially Paid</strong>
                        @else
                            <strong style="color: red;">Unpaid</strong>
                        @endif
                    </td>
                </tr>
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
            <p>This is an official statement of St. Francis Of Assisi Private School.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
