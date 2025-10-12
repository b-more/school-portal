<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee->name }} - {{ $payroll->month }} {{ $payroll->year }}</title>
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

        .payslip {
            max-width: 900px;
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
            position: relative;
        }

        .logo-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
            font-weight: bold;
            font-size: 24px;
            color: #667eea;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .school-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .school-info p {
            font-size: 14px;
            opacity: 0.95;
        }

        .payslip-title {
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .content {
            padding: 30px;
        }

        .info-grid {
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
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
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
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .amount {
            text-align: right;
            font-weight: 600;
            color: #222;
        }

        .statutory-row {
            background: #fff3cd;
        }

        .summary {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .summary-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .summary-card.gross {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .summary-card.deductions {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .summary-card.net {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 25px;
        }

        .summary-label {
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .summary-amount {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .summary-card.net .summary-amount {
            font-size: 42px;
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

            .payslip {
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
    <div class="payslip">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">SFA</div>
                <div class="school-info">
                    <h1>St. Francis of Assisi School</h1>
                    <p>P.O. Box 12345, Lusaka, Zambia</p>
                    <p>Email: info@sfaschool.zm | Phone: +260 211 123456</p>
                </div>
            </div>
            <div class="payslip-title">
                PAYSLIP FOR {{ strtoupper($payroll->month) }} {{ $payroll->year }}
            </div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Employee Information -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Employee Name</span>
                    <span class="info-value">{{ $payroll->employee->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Employee ID</span>
                    <span class="info-value">{{ $payroll->employee->employee_id }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payroll->employee->department ?? 'N/A')) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Position</span>
                    <span class="info-value">{{ $payroll->employee->position ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Pay Period</span>
                    <span class="info-value">{{ $payroll->month }} {{ $payroll->year }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Date</span>
                    <span class="info-value">{{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : 'Pending' }}</span>
                </div>
            </div>

            <!-- Earnings -->
            <h2 class="section-title">Earnings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="amount">Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Salary</td>
                        <td class="amount">{{ number_format($payroll->basic_salary, 2) }}</td>
                    </tr>
                    @if(!empty($payroll->allowances) && count($payroll->allowances) > 0)
                        @foreach($payroll->allowances as $allowance)
                        <tr>
                            <td>{{ $allowance['type'] }}</td>
                            <td class="amount">{{ number_format($allowance['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            <!-- Deductions -->
            <h2 class="section-title">Deductions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="amount">Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statutoryTypes = ['NAPSA', 'PAYE', 'NHIMA'];
                    @endphp
                    @if(!empty($payroll->deductions) && count($payroll->deductions) > 0)
                        @foreach($payroll->deductions as $deduction)
                        <tr class="{{ in_array($deduction['type'], $statutoryTypes) ? 'statutory-row' : '' }}">
                            <td>
                                {{ $deduction['type'] }}
                                @if(in_array($deduction['type'], $statutoryTypes))
                                    <small style="color: #856404;">(Statutory)</small>
                                @endif
                            </td>
                            <td class="amount">{{ number_format($deduction['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" style="text-align: center; color: #999;">No deductions</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Summary -->
            <div class="summary">
                <div class="summary-card gross">
                    <div class="summary-label">Gross Salary</div>
                    <div class="summary-amount">ZMW {{ number_format($payroll->gross_salary, 2) }}</div>
                </div>
                <div class="summary-card deductions">
                    <div class="summary-label">Total Deductions</div>
                    <div class="summary-amount">ZMW {{ number_format(collect($payroll->deductions)->sum('amount'), 2) }}</div>
                </div>
                <div class="summary-card net">
                    <div class="summary-label">Net Salary (Take Home)</div>
                    <div class="summary-amount">ZMW {{ number_format($payroll->net_salary, 2) }}</div>
                    <small style="opacity: 0.9;">{{ $payroll->payment_status === 'paid' ? 'PAID' : 'PENDING PAYMENT' }}</small>
                </div>
            </div>

            @if($payroll->notes)
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong>Notes:</strong> {{ $payroll->notes }}
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Generated on:</strong> {{ now()->format('d M Y, h:i A') }}</p>
            <p class="footer-note">
                This is a computer-generated payslip and does not require a signature. For any queries, please contact the HR Department.
            </p>
        </div>
    </div>

    <!-- Action Buttons (No Print) -->
    <div class="no-print">
        <button onclick="window.print()" class="btn">Print Payslip</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Back to Payroll</a>
    </div>
</body>
</html>
