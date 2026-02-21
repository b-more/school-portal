<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Fee Summary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.5;
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
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .header p {
            color: #6b7280;
            font-size: 10px;
        }
        .filters-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 9px;
        }
        .filters-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-card {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary-card.primary {
            background-color: #eff6ff;
            border-color: #2563eb;
        }
        .summary-card.success {
            background-color: #d1fae5;
            border-color: #059669;
        }
        .summary-card.danger {
            background-color: #fee2e2;
            border-color: #dc2626;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        .summary-subtitle {
            font-size: 8px;
            color: #9ca3af;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #1e40af;
            color: white;
            padding: 10px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .stats-table th {
            background-color: #eff6ff;
            color: #1e40af;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            font-weight: bold;
            border: 1px solid #bfdbfe;
        }
        .stats-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .stats-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .stats-table tfoot td {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
        }
        .chart-bar {
            height: 20px;
            background-color: #2563eb;
            border-radius: 3px;
        }
        .chart-container {
            background-color: #f9fafb;
            padding: 10px;
            border: 1px solid #e5e7eb;
            margin: 10px 0;
        }
        .chart-row {
            margin: 8px 0;
        }
        .chart-label {
            font-size: 9px;
            margin-bottom: 3px;
            color: #374151;
        }
        .chart-value {
            font-size: 8px;
            color: #6b7280;
        }
        .pie-chart {
            display: table;
            width: 100%;
            margin: 15px 0;
        }
        .pie-slice {
            display: table-cell;
            padding: 10px;
            text-align: center;
            border: 1px solid white;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
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
        .highlight-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 15px 0;
            font-size: 9px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
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
        <h2>Fee Collection Summary Report</h2>
        <p>Report Type: {{ $reportType }} | Generated on {{ $reportDate }}</p>
    </div>

    {{-- Applied Filters --}}
    @if(isset($filters) && count(array_filter($filters)) > 0)
    <div class="filters-box">
        <div class="filters-title">Applied Filters:</div>
        @if(!empty($filters['academic_year_id']))
            <span>Academic Year: {{ \App\Models\AcademicYear::find($filters['academic_year_id'])->name ?? 'N/A' }}</span> |
        @endif
        @if(!empty($filters['term_id']))
            <span>Term: {{ \App\Models\Term::find($filters['term_id'])->name ?? 'N/A' }}</span> |
        @endif
        @if(!empty($filters['grade_id']))
            <span>Grade: {{ \App\Models\Grade::find($filters['grade_id'])->name ?? 'N/A' }}</span> |
        @endif
        @if(!empty($filters['report_type']))
            <span>Type: {{ ucfirst(str_replace('_', ' ', $filters['report_type'])) }}</span>
        @endif
    </div>
    @endif

    {{-- Main Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-card primary">
            <div class="summary-label">Total Students</div>
            <div class="summary-value">{{ $summary['total_students'] }}</div>
            <div class="summary-subtitle">with fee records</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Total Fees</div>
            <div class="summary-value">ZMW {{ number_format($summary['total_fees'], 2) }}</div>
            <div class="summary-subtitle">expected amount</div>
        </div>
        <div class="summary-card success">
            <div class="summary-label">Collected</div>
            <div class="summary-value" style="color: #059669;">ZMW {{ number_format($summary['total_paid'], 2) }}</div>
            <div class="summary-subtitle">{{ $summary['total_fees'] > 0 ? round(($summary['total_paid'] / $summary['total_fees']) * 100, 1) : 0 }}% collection rate</div>
        </div>
        <div class="summary-card danger">
            <div class="summary-label">Outstanding</div>
            <div class="summary-value" style="color: #dc2626;">ZMW {{ number_format($summary['total_balance'], 2) }}</div>
            <div class="summary-subtitle">pending payment</div>
        </div>
    </div>

    {{-- Payment Status Distribution --}}
    <div class="section">
        <div class="section-title">Payment Status Distribution</div>

        <div class="pie-chart">
            <div class="pie-slice" style="background-color: #d1fae5; width: {{ $summary['total_students'] > 0 ? ($summary['paid_count'] / $summary['total_students']) * 100 : 0 }}%;">
                <div style="font-size: 16px; font-weight: bold; color: #059669;">{{ $summary['paid_count'] }}</div>
                <div style="font-size: 8px; color: #065f46;">PAID</div>
            </div>
            <div class="pie-slice" style="background-color: #fef3c7; width: {{ $summary['total_students'] > 0 ? ($summary['partial_count'] / $summary['total_students']) * 100 : 0 }}%;">
                <div style="font-size: 16px; font-weight: bold; color: #f59e0b;">{{ $summary['partial_count'] }}</div>
                <div style="font-size: 8px; color: #92400e;">PARTIAL</div>
            </div>
            <div class="pie-slice" style="background-color: #fee2e2; width: {{ $summary['total_students'] > 0 ? ($summary['unpaid_count'] / $summary['total_students']) * 100 : 0 }}%;">
                <div style="font-size: 16px; font-weight: bold; color: #dc2626;">{{ $summary['unpaid_count'] }}</div>
                <div style="font-size: 8px; color: #991b1b;">UNPAID</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 10px; font-size: 9px; color: #6b7280;">
            <span class="badge badge-success">{{ round(($summary['paid_count'] / max($summary['total_students'], 1)) * 100, 1) }}%</span>
            Paid |
            <span class="badge badge-warning">{{ round(($summary['partial_count'] / max($summary['total_students'], 1)) * 100, 1) }}%</span>
            Partial |
            <span class="badge badge-danger">{{ round(($summary['unpaid_count'] / max($summary['total_students'], 1)) * 100, 1) }}%</span>
            Unpaid
        </div>
    </div>

    {{-- Grade-wise Collection --}}
    @if($fees->count() > 0)
    <div class="section">
        <div class="section-title">Grade-wise Fee Collection Analysis</div>

        <table class="stats-table">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th class="text-right">Students</th>
                    <th class="text-right">Total Fees</th>
                    <th class="text-right">Collected</th>
                    <th class="text-right">Outstanding</th>
                    <th class="text-right">Collection Rate</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gradeGroups = $fees->groupBy(fn($f) => $f->grade->name ?? $f->student->grade->name ?? 'Unassigned');
                @endphp
                @foreach($gradeGroups->sortKeys() as $gradeName => $gradeFees)
                    @php
                        $gradeTotal = $gradeFees->sum(fn($f) => $f->feeStructure->total_fee ?? 0);
                        $gradePaid = $gradeFees->sum('amount_paid');
                        $gradeBalance = $gradeFees->sum('balance');
                        $gradeRate = $gradeTotal > 0 ? round(($gradePaid / $gradeTotal) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $gradeName }}</strong></td>
                        <td class="text-right">{{ $gradeFees->count() }}</td>
                        <td class="text-right">ZMW {{ number_format($gradeTotal, 2) }}</td>
                        <td class="text-right" style="color: #059669;">ZMW {{ number_format($gradePaid, 2) }}</td>
                        <td class="text-right" style="color: #dc2626;">ZMW {{ number_format($gradeBalance, 2) }}</td>
                        <td class="text-right">
                            <strong>{{ $gradeRate }}%</strong>
                            @if($gradeRate >= 80)
                                <span class="badge badge-success">Excellent</span>
                            @elseif($gradeRate >= 60)
                                <span class="badge badge-info">Good</span>
                            @elseif($gradeRate >= 40)
                                <span class="badge badge-warning">Fair</span>
                            @else
                                <span class="badge badge-danger">Poor</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ $summary['total_students'] }}</strong></td>
                    <td class="text-right"><strong>ZMW {{ number_format($summary['total_fees'], 2) }}</strong></td>
                    <td class="text-right"><strong>ZMW {{ number_format($summary['total_paid'], 2) }}</strong></td>
                    <td class="text-right"><strong>ZMW {{ number_format($summary['total_balance'], 2) }}</strong></td>
                    <td class="text-right"><strong>{{ $summary['total_fees'] > 0 ? round(($summary['total_paid'] / $summary['total_fees']) * 100, 1) : 0 }}%</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- Collection Performance --}}
    <div class="section">
        <div class="section-title">Collection Performance Visualization</div>

        <div class="chart-container">
            @php
                $collectionRate = $summary['total_fees'] > 0 ? ($summary['total_paid'] / $summary['total_fees']) * 100 : 0;
                $outstandingRate = 100 - $collectionRate;
            @endphp

            <div class="chart-row">
                <div class="chart-label">
                    <strong>Collected: ZMW {{ number_format($summary['total_paid'], 2) }}</strong>
                    <span class="chart-value">({{ round($collectionRate, 1) }}% of total)</span>
                </div>
                <div class="chart-bar" style="width: {{ $collectionRate }}%; background-color: #059669;"></div>
            </div>

            <div class="chart-row">
                <div class="chart-label">
                    <strong>Outstanding: ZMW {{ number_format($summary['total_balance'], 2) }}</strong>
                    <span class="chart-value">({{ round($outstandingRate, 1) }}% remaining)</span>
                </div>
                <div class="chart-bar" style="width: {{ $outstandingRate }}%; background-color: #dc2626;"></div>
            </div>
        </div>
    </div>

    {{-- Key Insights --}}
    <div class="section">
        <div class="section-title">Key Insights & Recommendations</div>

        <div class="highlight-box">
            <strong>Collection Performance:</strong>
            @if($collectionRate >= 80)
                Excellent! The collection rate of {{ round($collectionRate, 1) }}% indicates strong fee collection performance.
            @elseif($collectionRate >= 60)
                Good collection rate of {{ round($collectionRate, 1) }}%. Consider follow-up on partial payments.
            @elseif($collectionRate >= 40)
                Fair collection rate of {{ round($collectionRate, 1) }}%. Immediate follow-up required for outstanding balances.
            @else
                Collection rate of {{ round($collectionRate, 1) }}% requires urgent attention. Consider payment plans and reminders.
            @endif
        </div>

        <div style="margin-top: 15px; padding: 10px; background-color: #eff6ff; border: 1px solid #bfdbfe; font-size: 9px;">
            <strong style="color: #1e40af;">Summary Statistics:</strong>
            <ul style="margin: 8px 0 0 20px; line-height: 1.8;">
                <li>{{ $summary['paid_count'] }} students ({{ round(($summary['paid_count'] / max($summary['total_students'], 1)) * 100, 1) }}%) have fully paid their fees</li>
                <li>{{ $summary['partial_count'] }} students ({{ round(($summary['partial_count'] / max($summary['total_students'], 1)) * 100, 1) }}%) have made partial payments</li>
                <li>{{ $summary['unpaid_count'] }} students ({{ round(($summary['unpaid_count'] / max($summary['total_students'], 1)) * 100, 1) }}%) have unpaid fees requiring immediate attention</li>
                <li>Average fee per student: ZMW {{ $summary['total_students'] > 0 ? number_format($summary['total_fees'] / $summary['total_students'], 2) : '0.00' }}</li>
                <li>Average payment per student: ZMW {{ $summary['total_students'] > 0 ? number_format($summary['total_paid'] / $summary['total_students'], 2) : '0.00' }}</li>
            </ul>
        </div>
    </div>

    {{-- Recommendations --}}
    <div style="margin-top: 20px; padding: 12px; border: 2px solid #2563eb; background-color: #eff6ff;">
        <strong style="color: #1e40af; font-size: 11px;">Recommended Actions:</strong>
        <ol style="margin: 8px 0 0 20px; line-height: 1.8; font-size: 9px;">
            @if($summary['unpaid_count'] > 0)
                <li>Send payment reminders to {{ $summary['unpaid_count'] }} students with unpaid fees</li>
            @endif
            @if($summary['partial_count'] > 0)
                <li>Follow up with {{ $summary['partial_count'] }} students who have made partial payments</li>
            @endif
            @if($summary['total_balance'] > 0)
                <li>Consider offering payment plans for outstanding balance of ZMW {{ number_format($summary['total_balance'], 2) }}</li>
            @endif
            <li>Review and update fee collection policies if collection rate is below target</li>
            <li>Schedule parent meetings for students with long-outstanding balances</li>
        </ol>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>{{ $schoolName }}</strong></p>
        <p>Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
        <p>Phone: +260 972 266 217 | Email: info@stfrancisofassisi.tech</p>
        <p style="margin-top: 10px;">Generated on {{ $reportDate }} | Confidential - For Internal Use Only</p>
    </div>
</body>
</html>
