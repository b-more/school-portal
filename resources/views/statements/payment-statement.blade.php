<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Statement - {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #fff;
            font-size: 12px;
        }

        .statement-container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border: 2px solid #e0e6ed;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: #1a365d;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            color: #4a90e2;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a365d;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
        }

        .school-tagline {
            font-size: 14px;
            margin-bottom: 10px;
            color: #2c5282;
        }

        .contact-info {
            font-size: 11px;
            color: #2c5282;
            line-height: 1.4;
        }

        .statement-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 3px solid #4a90e2;
        }

        .statement-title {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
            letter-spacing: 1px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        .info-group h4 {
            margin: 0 0 8px 0;
            color: #4a90e2;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            border-bottom: 1px solid #e0e6ed;
            padding-bottom: 3px;
        }

        .info-group p {
            margin: 3px 0;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .info-group p strong {
            color: #2c3e50;
        }

        .content {
            padding: 20px;
        }

        .summary-section {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 25px;
            margin-bottom: 20px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #4a90e2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-card.outstanding {
            border-left-color: #e74c3c;
        }

        .summary-card.paid {
            border-left-color: #27ae60;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .collection-rate {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .rate-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .rate-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            border: 1px solid #e0e6ed;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .payment-table th {
            background: #4a90e2;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .payment-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f0f0f0;
            text-align: center;
            font-size: 12px;
            color: #333;
        }

        .payment-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .payment-table tr:hover {
            background-color: #e8f4fd;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .amount {
            font-weight: 600;
        }

        .amount.positive {
            color: #27ae60;
        }

        .amount.negative {
            color: #e74c3c;
        }

        .progress-bar {
            width: 80px;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            overflow: hidden;
            margin: 2px auto;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-paid {
            background: #27ae60;
        }

        .progress-partial {
            background: #f39c12;
        }

        .footer-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e6ed;
        }

        .payment-summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4a90e2;
        }

        .balance-status {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        .balance-due {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .balance-clear {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 11px;
        }

        .footer-note {
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .contact-details {
            opacity: 0.9;
            line-height: 1.3;
        }

        .no-history {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-size: 14px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #dee2e6;
        }

        @media print {
            body {
                padding: 0;
                font-size: 11px;
            }

            .statement-container {
                border: 1px solid #ccc;
                box-shadow: none;
            }

            .payment-table {
                font-size: 10px;
            }

            .payment-table th,
            .payment-table td {
                padding: 6px 4px;
            }

            .payment-table tr:hover {
                background-color: transparent;
            }
        }

        @media screen and (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .summary-section {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .summary-cards {
                grid-template-columns: 1fr;
            }

            .footer-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="statement-container">
        {{-- Header --}}
        <div class="header">
            <div class="header-content">
                @if(file_exists($school_info['logo_path']))
                    <img src="{{ $school_info['logo_path'] }}" alt="School Logo" class="logo">
                @else
                    <div class="logo">SF</div>
                @endif

                <div class="school-name">{{ $school_info['name'] }}</div>
                <div class="school-tagline">Excellence in Education</div>
                <div class="contact-info">
                    {{ $school_info['address'] }}<br>
                    Phone: {{ $school_info['phone'] }} | Email: {{ $school_info['email'] }}
                </div>
            </div>
        </div>

        {{-- Statement Header --}}
        <div class="statement-header">
            <div class="statement-title">PAYMENT STATEMENT</div>

            <div class="info-grid">
                <div class="info-group">
                    <h4>Student Information</h4>
                    <p><strong>{{ $student->name }}</strong></p>
                    <p>Student ID: {{ $student->student_id_number ?? 'Not assigned' }}</p>
                    <p>Grade: {{ $student->grade->name ?? 'Not assigned' }}</p>
                    @if($student->classSection)
                        <p>Section: {{ $student->classSection->name }}</p>
                    @endif
                </div>

                @if($parent_guardian)
                <div class="info-group">
                    <h4>Parent/Guardian</h4>
                    <p><strong>{{ $parent_guardian->name }}</strong></p>
                    <p>Phone: {{ $parent_guardian->phone }}</p>
                    @if($parent_guardian->email)
                        <p>Email: {{ $parent_guardian->email }}</p>
                    @endif
                    <p>Relationship: {{ ucfirst($parent_guardian->relationship ?? 'Guardian') }}</p>
                </div>
                @endif

                <div class="info-group">
                    <h4>Statement Details</h4>
                    <p>Statement #: {{ $statement_number }}</p>
                    <p>Period: {{ $period_description }}</p>
                    <p>Generated: {{ $generated_at->format('M j, Y') }}</p>
                    @if($current_term)
                        <p>Current Term: {{ $current_term->name }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Summary Section --}}
            <div class="summary-section">
                <div class="summary-cards">
                    <div class="summary-card">
                        <div class="summary-value">ZMW {{ number_format($summary['total_fees_charged'], 2) }}</div>
                        <div class="summary-label">Total Fees Charged</div>
                    </div>

                    <div class="summary-card paid">
                        <div class="summary-value">ZMW {{ number_format($summary['total_payments_made'], 2) }}</div>
                        <div class="summary-label">Total Payments Made</div>
                    </div>

                    <div class="summary-card outstanding">
                        <div class="summary-value">ZMW {{ number_format($summary['total_outstanding'], 2) }}</div>
                        <div class="summary-label">Outstanding Balance</div>
                    </div>

                    <div class="summary-card">
                        <div class="summary-value">{{ $summary['number_of_terms'] }}</div>
                        <div class="summary-label">Terms Covered</div>
                    </div>
                </div>

                <div class="collection-rate">
                    <div class="rate-value">{{ number_format($summary['collection_rate'], 1) }}%</div>
                    <div class="rate-label">Collection Rate</div>
                </div>
            </div>

            {{-- Payment History --}}
            @if(empty($payment_history))
                <div class="no-history">
                    <p><strong>No payment records found</strong></p>
                    <p>Payment history will appear here once fees are assigned and payments are made.</p>
                </div>
            @else
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Academic Year</th>
                            <th>Fee Amount</th>
                            <th>Amount Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment_history as $record)
                            <tr>
                                <td><strong>{{ $record['term'] }}</strong></td>
                                <td>{{ $record['academic_year'] }}</td>
                                <td class="amount">ZMW {{ number_format($record['total_fee'], 2) }}</td>
                                <td class="amount positive">ZMW {{ number_format($record['amount_paid'], 2) }}</td>
                                <td class="amount {{ $record['balance'] > 0 ? 'negative' : 'positive' }}">
                                    ZMW {{ number_format($record['balance'], 2) }}
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $record['payment_status'] }}">
                                        {{ ucfirst($record['payment_status']) }}
                                    </span>
                                </td>
                                <td>
                                    @if($record['payment_date'])
                                        {{ \Carbon\Carbon::parse($record['payment_date'])->format('M j, Y') }}
                                    @else
                                        <span style="color: #7f8c8d;">Not paid</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $progress = $record['total_fee'] > 0 ? ($record['amount_paid'] / $record['total_fee']) * 100 : 0;
                                        $progress = min(100, max(0, $progress));
                                    @endphp
                                    <div class="progress-bar">
                                        <div class="progress-fill progress-{{ $progress >= 100 ? 'paid' : 'partial' }}"
                                             style="width: {{ $progress }}%"></div>
                                    </div>
                                    <small>{{ number_format($progress, 1) }}%</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Footer Summary --}}
                <div class="footer-section">
                    <div class="payment-summary-box">
                        <strong>Payment Summary</strong>
                        <p>{{ $summary['number_of_terms'] }} term(s) covered in this statement</p>
                        <p>Collection Rate: {{ number_format($summary['collection_rate'], 1) }}%</p>
                    </div>

                    <div class="balance-status {{ $summary['total_outstanding'] > 0 ? 'balance-due' : 'balance-clear' }}">
                        @if($summary['total_outstanding'] > 0)
                            <div>PAYMENT REQUIRED</div>
                            <div style="font-size: 16px; margin-top: 5px;">ZMW {{ number_format($summary['total_outstanding'], 2) }}</div>
                        @else
                            <div>ACCOUNT UP TO DATE</div>
                            <div style="margin-top: 5px;">✓ All Payments Current</div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="footer-note">
                <strong>Official Statement:</strong> This document serves as an official record of payment history with {{ $school_info['name'] }}.
                Please retain this document for your records.
            </div>
            <div class="contact-details">
                For payment inquiries, contact {{ $school_info['phone'] }} or {{ $school_info['email'] }}<br>
                Generated: {{ $generated_at->format('F j, Y \a\t g:i A') }} | Statement #{{ $statement_number }}
            </div>
        </div>
    </div>
</body>
</html>
