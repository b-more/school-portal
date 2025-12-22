<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Payslip - {{ $payroll->employee->name }} - {{ $payroll->month }} {{ $payroll->year }}</title>
    <style>
        @page {
            margin: 20mm 15mm;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
            position: relative;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            z-index: -1;
            width: 300px;
            height: auto;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
        }

        .logo-container {
            margin-bottom: 3px;
        }

        .logo {
            width: 50px;
            height: auto;
        }

        .school-name {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .document-title {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-top: 2px;
        }

        /* Info Grid - Top Section */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .info-grid th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
            padding: 4px 6px;
            font-size: 8px;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        .info-grid td {
            text-align: center;
            padding: 4px 6px;
            font-size: 9px;
            border: 1px solid #000;
            background-color: #fff;
        }

        /* Employee Details Grid */
        .details-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .details-grid td {
            padding: 3px 6px;
            font-size: 8px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .details-grid .label {
            font-weight: bold;
            color: #000;
            width: 18%;
            background-color: #f5f5f5;
        }

        .details-grid .value {
            width: 32%;
        }

        /* Payment/Deduction Table */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .payment-table th {
            background-color: #d0d0d0;
            color: #000;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            font-size: 8px;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        .payment-table th.amount {
            text-align: right;
        }

        .payment-table td {
            padding: 3px 6px;
            font-size: 8px;
            border: 1px solid #000;
            background-color: #fff;
        }

        .payment-table td.amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        .payment-table td.code {
            text-align: center;
            font-weight: bold;
        }

        .payment-table tr.totals-row td {
            font-weight: bold;
            background-color: #e8e8e8;
            border-top: 2px solid #000;
        }

        .payment-table tr.net-pay-row td {
            font-weight: bold;
            background-color: #d0d0d0;
            font-size: 10px;
            border: 2px solid #000;
        }

        /* Footer */
        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 8px;
            color: #333;
            padding-top: 6px;
            border-top: 1px solid #000;
        }

        .footer p {
            margin: 2px 0;
        }

        .footer .disclaimer {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    @if(file_exists(public_path('images/logo.png')))
        <img src="{{ public_path('images/logo.png') }}" alt="" class="watermark">
    @endif

    <!-- Header -->
    <div class="header">
        <div class="logo-container">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
            @endif
        </div>
        <div class="school-name">St. Francis of Assisi Private School</div>
        <div class="document-title">E-Payslip</div>
    </div>

    @php
        // Get employee data
        $employee = $payroll->employee;

        // Present Appointment Date (designation changed date or joining date)
        $presentAppointment = $employee->designation_changed_date ?? $employee->joining_date;

        // Next Appraisal Date (one year after present appointment)
        $nextAppraisal = null;
        if ($presentAppointment) {
            $nextAppraisal = \Carbon\Carbon::parse($presentAppointment)->addYear();
            while ($nextAppraisal->isPast()) {
                $nextAppraisal->addYear();
            }
        }

        // Get salary grade info
        $salaryGrade = $employee->salaryGrade;
        $gradeCode = $salaryGrade ? $salaryGrade->code : 'N/A';

        // Leave calculations
        $leaveAccrued = 0;
        $leaveTaken = 0;
        $currentYear = date('Y');

        if ($employee->leaveBalances) {
            $leaveAccrued = $employee->leaveBalances()
                ->where('year', $currentYear)
                ->sum('allocated_days');
            $leaveTaken = $employee->leaveBalances()
                ->where('year', $currentYear)
                ->sum('used_days');
        }
    @endphp

    <!-- Top Info Grid -->
    <table class="info-grid">
        <tr>
            <th style="width: 33%;">Pay Month</th>
            <th style="width: 34%;">Next Appraisal</th>
            <th style="width: 33%;">Present Appoint</th>
        </tr>
        <tr>
            <td>{{ $payroll->month }} {{ $payroll->year }}</td>
            <td>{{ $nextAppraisal ? $nextAppraisal->format('d.m.Y') : 'N/A' }}</td>
            <td>{{ $presentAppointment ? \Carbon\Carbon::parse($presentAppointment)->format('d.m.Y') : 'N/A' }}</td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <th style="width: 25%;">Employee No.</th>
            <th style="width: 25%;">NRC</th>
            <th style="width: 25%;">TPIN</th>
            <th style="width: 25%;">Sal/Scale</th>
        </tr>
        <tr>
            <td>{{ $employee->employee_number ?? $employee->employee_id ?? 'N/A' }}</td>
            <td>{{ $employee->nrc_number ?? 'N/A' }}</td>
            <td>{{ $employee->tpin_number ?? 'N/A' }}</td>
            <td>{{ $gradeCode }}</td>
        </tr>
    </table>

    <!-- Employee Details Grid -->
    <table class="details-grid">
        <tr>
            <td class="label">Employee Name</td>
            <td class="value" colspan="3">{{ strtoupper($employee->name) }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td>
            <td class="value">{{ $employee->position ?? 'N/A' }}</td>
            <td class="label">Bank Name</td>
            <td class="value">{{ strtoupper($employee->bank_name ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td class="value">{{ ucfirst(str_replace('_', ' ', $employee->department ?? 'N/A')) }}</td>
            <td class="label">Account No.</td>
            <td class="value">{{ $employee->bank_account_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Leave Accrued</td>
            <td class="value">{{ number_format($leaveAccrued, 2) }}</td>
            <td class="label">Leave Taken</td>
            <td class="value">{{ number_format($leaveTaken, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Payment Method</td>
            <td class="value">EFT Payment</td>
            <td class="label">Taxable Income</td>
            <td class="value">{{ number_format($payroll->gross_salary, 2) }}</td>
        </tr>
    </table>

    <!-- Payment/Deduction Table -->
    @php
        $allowances = $payroll->allowances ?? [];
        $deductions = $payroll->deductions ?? [];
        $totalAllowances = collect($allowances)->sum('amount');
        $totalDeductions = collect($deductions)->sum('amount');

        // Custom codes for St. Francis School
        $allowanceCodes = [
            'Housing Allowance' => '2001',
            'Transport Allowance' => '2002',
            'Meal Allowance' => '2003',
            'Medical Allowance' => '2004',
            'Overtime' => '2005',
            'Bonus' => '2006',
            'Other' => '2099',
        ];

        // Deduction codes
        $deductionCodes = [
            'NAPSA' => 'D001',
            'PAYE' => 'D002',
            'NHIMA' => 'D003',
            'Loan Repayment' => 'D004',
            'Advance Repayment' => 'D005',
            'Absence Deduction' => 'D006',
            'Other' => 'D099',
        ];

        // Accumulation tracking (simulated - in real scenario this would come from payroll history)
        $accumulatedBasic = $payroll->basic_salary;
        $accumulatedAllowances = [];
        $accumulatedDeductions = [];

        // Combine all items for the table
        $allItems = [];

        // Add Basic Salary first
        $allItems[] = [
            'code' => '1000',
            'description' => 'Basic Salary',
            'period' => '999',
            'payment' => $payroll->basic_salary,
            'deduction' => 0,
            'accumulation' => $accumulatedBasic,
        ];

        // Add allowances
        foreach ($allowances as $allowance) {
            $allItems[] = [
                'code' => $allowanceCodes[$allowance['type']] ?? '2099',
                'description' => $allowance['type'],
                'period' => '999',
                'payment' => $allowance['amount'],
                'deduction' => 0,
                'accumulation' => $allowance['amount'],
            ];
        }

        // Add deductions
        foreach ($deductions as $deduction) {
            $allItems[] = [
                'code' => $deductionCodes[$deduction['type']] ?? 'D099',
                'description' => $deduction['type'],
                'period' => '999',
                'payment' => 0,
                'deduction' => $deduction['amount'],
                'accumulation' => $deduction['amount'],
            ];
        }
    @endphp

    <table class="payment-table">
        <thead>
            <tr>
                <th style="width: 8%;">Code</th>
                <th style="width: 32%;">Description</th>
                <th style="width: 8%;">Period</th>
                <th style="width: 17%;" class="amount">Payment Amount</th>
                <th style="width: 17%;" class="amount">Deduction Amount</th>
                <th style="width: 18%;" class="amount">Accumulation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allItems as $item)
            <tr>
                <td class="code">{{ $item['code'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td style="text-align: center;">{{ $item['period'] }}</td>
                <td class="amount">
                    {{ $item['payment'] > 0 ? number_format($item['payment'], 2) : '' }}
                </td>
                <td class="amount">
                    {{ $item['deduction'] > 0 ? number_format($item['deduction'], 2) . '-' : '' }}
                </td>
                <td class="amount">
                    @if($item['deduction'] > 0)
                        {{ number_format($item['accumulation'], 2) }}-
                    @else
                        {{ number_format($item['accumulation'], 2) }}
                    @endif
                </td>
            </tr>
            @endforeach

            <!-- Totals Row -->
            <tr class="totals-row">
                <td colspan="2" style="text-align: center; font-weight: bold;">Totals</td>
                <td></td>
                <td class="amount">{{ number_format($payroll->gross_salary, 2) }}</td>
                <td class="amount">{{ number_format($totalDeductions, 2) }}-</td>
                <td class="amount"></td>
            </tr>

            <!-- Net Pay Row -->
            <tr class="net-pay-row">
                <td colspan="2" style="text-align: center; font-weight: bold;">NET PAY</td>
                <td></td>
                <td class="amount" colspan="3" style="text-align: center;">
                    {{ number_format($payroll->net_salary, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Notes Section (if any) -->
    @if($payroll->notes)
    <div style="margin: 8px 0; padding: 6px; background-color: #f5f5f5; border-left: 3px solid #000; font-size: 8px;">
        <strong>Notes:</strong> {{ $payroll->notes }}
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p class="disclaimer">This is a computer-generated payslip and is valid without a signature.</p>
        <p>Any discrepancies should be reported to the HR Department within 7 days.</p>
        <p style="margin-top: 4px; font-size: 7px;">
            St. Francis of Assisi Private School | 1310/4 East Kamenza, Chililabombwe, Zambia | Tel: +260 972 266 217
        </p>
        <p style="font-size: 7px;">Generated on: {{ now()->format('d M Y, H:i:s') }}</p>
    </div>
</body>
</html>
