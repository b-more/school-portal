<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Payment Receipt - {{ $busPayment->student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }

        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .school-info h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .school-info p {
            font-size: 13px;
            opacity: 0.95;
        }

        .receipt-title {
            font-size: 28px;
            font-weight: 700;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .content {
            padding: 30px;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 14px;
            color: #222;
            font-weight: 500;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            font-size: 14px;
        }

        .amount {
            text-align: right;
            font-weight: 600;
        }

        .totals {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 15px;
        }

        .total-row.grand {
            border-top: 2px solid #667eea;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-partial {
            background: #fff3cd;
            color: #856404;
        }

        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }

        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            border-top: 3px solid #667eea;
            font-size: 12px;
            color: #666;
        }

        .footer-note {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-style: italic;
        }

        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.4);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
                border-radius: 0;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 0.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    @if(file_exists(public_path('logo.png')))
                        <img src="{{ asset('logo.png') }}" alt="School Logo">
                    @else
                        <div style="color: #667eea; font-weight: bold; font-size: 24px;">SFA</div>
                    @endif
                </div>
                <div class="school-info">
                    <h1>St. Francis of Assisi School</h1>
                    <p>P.O. Box 12345, Lusaka, Zambia</p>
                    <p>Email: info@sfaschool.zm | Phone: +260 211 123456</p>
                </div>
            </div>
            <div class="receipt-title">BUS PAYMENT RECEIPT</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Receipt Information -->
            <div class="receipt-info">
                <div class="info-item">
                    <span class="info-label">Receipt Number</span>
                    <span class="info-value">BUS-{{ str_pad($busPayment->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Receipt Date</span>
                    <span class="info-value">{{ now()->format('d M Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Student Name</span>
                    <span class="info-value">{{ $busPayment->student->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Student ID</span>
                    <span class="info-value">{{ $busPayment->student->student_id_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Grade</span>
                    <span class="info-value">{{ $busPayment->student->grade->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $busPayment->payment_status }}">
                            {{ strtoupper($busPayment->payment_status) }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- Payment Details -->
            <h2 class="section-title">Payment Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Period</th>
                        <th class="amount">Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $busPayment->busFareStructure->route_name }}</strong><br>
                            <small style="color: #666;">{{ $busPayment->busFareStructure->payment_plan === 'monthly' ? 'Monthly Plan' : 'Per Term Plan' }}</small>
                        </td>
                        <td>
                            @if($busPayment->month)
                                {{ $busPayment->month }} {{ $busPayment->year }}
                            @else
                                Full Term {{ $busPayment->year }}
                            @endif
                        </td>
                        <td class="amount">{{ number_format($busPayment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals">
                <div class="total-row">
                    <span>Total Amount</span>
                    <span>ZMW {{ number_format($busPayment->amount, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Amount Paid</span>
                    <span style="color: #28a745;">ZMW {{ number_format($busPayment->amount_paid, 2) }}</span>
                </div>
                @if($busPayment->balance > 0)
                <div class="total-row">
                    <span>Balance Outstanding</span>
                    <span style="color: #dc3545;">ZMW {{ number_format($busPayment->balance, 2) }}</span>
                </div>
                @endif
                <div class="total-row grand">
                    <span>NET AMOUNT</span>
                    <span>ZMW {{ number_format($busPayment->amount_paid, 2) }}</span>
                </div>
            </div>

            @if($busPayment->notes)
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong>Notes:</strong><br>
                {{ $busPayment->notes }}
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Payment Method:</strong> {{ $busPayment->payment_status === 'paid' ? 'Paid in Full' : 'Partial Payment' }}</p>
            <p><strong>Generated on:</strong> {{ now()->format('d M Y, h:i A') }}</p>
            <p class="footer-note">
                This is an official receipt from St. Francis of Assisi School. Please keep this receipt for your records. For any queries, please contact the school administration.
            </p>
        </div>
    </div>

    <!-- Action Buttons (No Print) -->
    <div class="no-print">
        <button onclick="window.print()" class="btn">Print Receipt</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>
