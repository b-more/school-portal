<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Fees List</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 18px;
            margin-bottom: 3px;
        }
        .header h2 {
            color: #1e40af;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .header p {
            color: #6b7280;
            font-size: 9px;
        }
        .summary-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .summary-box h3 {
            color: #1e40af;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            padding: 8px;
            text-align: center;
            border-right: 1px solid #bfdbfe;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }
        .summary-value.success {
            color: #059669;
        }
        .summary-value.danger {
            color: #dc2626;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
        }
        .data-table td {
            padding: 5px 4px;
            border: 1px solid #e5e7eb;
            font-size: 8px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table tfoot td {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            padding: 8px 4px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
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
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
        }
        .page-break {
            page-break-after: always;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Fees Collection Report</h2>
        <p>Report Type: {{ $reportType }} | Generated on {{ $reportDate }}</p>
    </div>

    {{-- Summary Statistics --}}
    <div class="summary-box">
        <h3>Financial Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Students</div>
                <div class="summary-value">{{ $fees->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Fees</div>
                <div class="summary-value">ZMW {{ number_format($totalAmount, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Amount Paid</div>
                <div class="summary-value success">ZMW {{ number_format($totalPaid, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Outstanding</div>
                <div class="summary-value danger">ZMW {{ number_format($totalBalance, 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Collection Rate</div>
                <div class="summary-value">{{ $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0 }}%</div>
            </div>
        </div>
    </div>

    {{-- Payment Status Breakdown --}}
    <div style="margin-bottom: 15px; padding: 8px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
        <div style="display: table; width: 100%; font-size: 8px;">
            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                <span class="badge badge-success">Paid</span>: {{ $fees->where('payment_status', 'paid')->count() }}
            </div>
            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                <span class="badge badge-warning">Partial</span>: {{ $fees->where('payment_status', 'partial')->count() }}
            </div>
            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                <span class="badge badge-danger">Unpaid</span>: {{ $fees->where('payment_status', 'unpaid')->count() }}
            </div>
            <div style="display: table-cell; width: 25%; text-align: center; padding: 5px;">
                <span class="badge badge-info">Overpaid</span>: {{ $fees->where('payment_status', 'overpaid')->count() }}
            </div>
        </div>
    </div>

    {{-- Fees Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 7%;">ID</th>
                <th style="width: 18%;">Student Name</th>
                <th style="width: 10%;">Grade</th>
                <th style="width: 10%;">Term</th>
                <th style="width: 10%;">Total Fee</th>
                <th style="width: 10%;">Paid</th>
                <th style="width: 10%;">Balance</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Payment Date</th>
                <th style="width: 10%;">Contact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fees as $index => $fee)
            <tr>
                <td>{{ $fee->student->student_id_number }}</td>
                <td>{{ $fee->student->name }}</td>
                <td>{{ $fee->grade->name ?? $fee->student->grade->name ?? 'N/A' }}</td>
                <td>{{ $fee->term->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($fee->feeStructure->total_fee ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($fee->amount_paid, 2) }}</td>
                <td class="text-right" style="color: {{ $fee->balance > 0 ? '#dc2626' : '#059669' }};">
                    {{ number_format($fee->balance, 2) }}
                </td>
                <td>
                    @if($fee->payment_status === 'paid')
                        <span class="badge badge-success">Paid</span>
                    @elseif($fee->payment_status === 'partial')
                        <span class="badge badge-warning">Partial</span>
                    @elseif($fee->payment_status === 'overpaid')
                        <span class="badge badge-info">Overpaid</span>
                    @else
                        <span class="badge badge-danger">Unpaid</span>
                    @endif
                </td>
                <td>{{ $fee->payment_date ? $fee->payment_date->format('d/m/Y') : '-' }}</td>
                <td>{{ $fee->student->parentGuardian->phone ?? 'N/A' }}</td>
            </tr>
            @if(($index + 1) % 25 === 0 && $index + 1 < $fees->count())
                </tbody>
            </table>
            <div class="page-break"></div>

            {{-- Repeat header on new page --}}
            <div class="header">
                <h1>{{ $schoolName }}</h1>
                <h2>Fees Collection Report (Continued)</h2>
                <p>Page {{ ceil(($index + 1) / 25) + 1 }}</p>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 7%;">ID</th>
                        <th style="width: 18%;">Student Name</th>
                        <th style="width: 10%;">Grade</th>
                        <th style="width: 10%;">Term</th>
                        <th style="width: 10%;">Total Fee</th>
                        <th style="width: 10%;">Paid</th>
                        <th style="width: 10%;">Balance</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 10%;">Payment Date</th>
                        <th style="width: 10%;">Contact</th>
                    </tr>
                </thead>
                <tbody>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right"><strong>ZMW {{ number_format($totalAmount, 2) }}</strong></td>
                <td class="text-right"><strong>ZMW {{ number_format($totalPaid, 2) }}</strong></td>
                <td class="text-right"><strong>ZMW {{ number_format($totalBalance, 2) }}</strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>

    {{-- Grade-wise Summary --}}
    @if($fees->count() > 0)
    <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 10px; color: #1e40af; margin-bottom: 8px;">Collection by Grade</h3>
        <table style="width: 100%; font-size: 8px; border-collapse: collapse;">
            <tr style="background-color: #e5e7eb;">
                <th style="padding: 5px; text-align: left;">Grade</th>
                <th style="padding: 5px; text-align: right;">Students</th>
                <th style="padding: 5px; text-align: right;">Total Fee</th>
                <th style="padding: 5px; text-align: right;">Collected</th>
                <th style="padding: 5px; text-align: right;">Outstanding</th>
                <th style="padding: 5px; text-align: right;">Rate</th>
            </tr>
            @php
                $gradeGroups = $fees->groupBy(fn($f) => $f->grade->name ?? $f->student->grade->name ?? 'Unassigned');
            @endphp
            @foreach($gradeGroups as $gradeName => $gradeFees)
                @php
                    $gradeTotal = $gradeFees->sum(fn($f) => $f->feeStructure->total_fee ?? 0);
                    $gradePaid = $gradeFees->sum('amount_paid');
                    $gradeBalance = $gradeFees->sum('balance');
                    $gradeRate = $gradeTotal > 0 ? round(($gradePaid / $gradeTotal) * 100, 1) : 0;
                @endphp
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 5px;"><strong>{{ $gradeName }}</strong></td>
                    <td style="padding: 5px; text-align: right;">{{ $gradeFees->count() }}</td>
                    <td style="padding: 5px; text-align: right;">ZMW {{ number_format($gradeTotal, 2) }}</td>
                    <td style="padding: 5px; text-align: right; color: #059669;">ZMW {{ number_format($gradePaid, 2) }}</td>
                    <td style="padding: 5px; text-align: right; color: #dc2626;">ZMW {{ number_format($gradeBalance, 2) }}</td>
                    <td style="padding: 5px; text-align: right;">{{ $gradeRate }}%</td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p><strong>{{ $schoolName }}</strong></p>
        <p>Generated on {{ $reportDate }} | Total Records: {{ $fees->count() }}</p>
        <p>This is a computer-generated document | For official use only</p>
    </div>
</body>
</html>
