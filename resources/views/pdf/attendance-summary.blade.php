<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Attendance Summary</title>
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
        .period-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
        .period-box strong {
            color: #1e40af;
            font-size: 11px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 25px;
        }
        .summary-card {
            display: table-cell;
            padding: 15px;
            text-align: center;
            border: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary-card.present {
            background-color: #d1fae5;
            border-color: #059669;
        }
        .summary-card.absent {
            background-color: #fee2e2;
            border-color: #dc2626;
        }
        .summary-card.late {
            background-color: #fef3c7;
            border-color: #f59e0b;
        }
        .summary-card.excused {
            background-color: #dbeafe;
            border-color: #2563eb;
        }
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        .summary-subtitle {
            font-size: 8px;
            color: #6b7280;
        }
        .attendance-rate-box {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .attendance-rate-box .rate {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .attendance-rate-box .label {
            font-size: 12px;
            opacity: 0.9;
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
        .chart-container {
            background-color: #f9fafb;
            padding: 15px;
            border: 1px solid #e5e7eb;
            margin: 10px 0;
        }
        .chart-row {
            margin: 10px 0;
        }
        .chart-label {
            font-size: 10px;
            margin-bottom: 5px;
            color: #374151;
            font-weight: bold;
        }
        .chart-bar-bg {
            background-color: #e5e7eb;
            height: 25px;
            border-radius: 5px;
            overflow: hidden;
        }
        .chart-bar {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 9px;
            font-weight: bold;
        }
        .pie-chart {
            display: table;
            width: 100%;
            margin: 15px 0;
            border-radius: 5px;
            overflow: hidden;
        }
        .pie-slice {
            display: table-cell;
            padding: 15px;
            text-align: center;
            color: white;
        }
        .pie-slice .value {
            font-size: 20px;
            font-weight: bold;
        }
        .pie-slice .label {
            font-size: 9px;
            margin-top: 5px;
            opacity: 0.9;
        }
        .insight-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 15px 0;
            font-size: 9px;
        }
        .success-box {
            background-color: #d1fae5;
            border-left: 4px solid #059669;
            padding: 12px;
            margin: 15px 0;
            font-size: 9px;
        }
        .danger-box {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
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
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Attendance Summary Report</h2>
        <p>Generated on {{ $reportDate }}</p>
    </div>

    {{-- Report Period --}}
    @if($dateFrom || $dateTo)
    <div class="period-box">
        <strong>Analysis Period:</strong>
        @if($dateFrom && $dateTo)
            {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        @elseif($dateFrom)
            From {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }}
        @elseif($dateTo)
            Up to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        @endif
    </div>
    @endif

    {{-- Overall Attendance Rate --}}
    @php
        $attendanceRate = $summary['total_records'] > 0 ? round(($summary['present_count'] / $summary['total_records']) * 100, 1) : 0;
    @endphp
    <div class="attendance-rate-box">
        <div class="rate">{{ $attendanceRate }}%</div>
        <div class="label">OVERALL ATTENDANCE RATE</div>
    </div>

    {{-- Status Summary Cards --}}
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ number_format($summary['total_records']) }}</div>
            <div class="summary-subtitle">attendance entries</div>
        </div>
        <div class="summary-card present">
            <div class="summary-label">Present</div>
            <div class="summary-value" style="color: #065f46;">{{ number_format($summary['present_count']) }}</div>
            <div class="summary-subtitle">{{ round(($summary['present_count'] / max($summary['total_records'], 1)) * 100, 1) }}% of total</div>
        </div>
        <div class="summary-card absent">
            <div class="summary-label">Absent</div>
            <div class="summary-value" style="color: #991b1b;">{{ number_format($summary['absent_count']) }}</div>
            <div class="summary-subtitle">{{ round(($summary['absent_count'] / max($summary['total_records'], 1)) * 100, 1) }}% of total</div>
        </div>
        <div class="summary-card late">
            <div class="summary-label">Late Arrivals</div>
            <div class="summary-value" style="color: #92400e;">{{ number_format($summary['late_count']) }}</div>
            <div class="summary-subtitle">{{ round(($summary['late_count'] / max($summary['total_records'], 1)) * 100, 1) }}% of total</div>
        </div>
    </div>

    {{-- Status Distribution Chart --}}
    <div class="section">
        <div class="section-title">Attendance Status Distribution</div>

        <div class="pie-chart">
            <div class="pie-slice" style="background-color: #059669; width: {{ ($summary['present_count'] / max($summary['total_records'], 1)) * 100 }}%;">
                <div class="value">{{ $summary['present_count'] }}</div>
                <div class="label">PRESENT</div>
            </div>
            <div class="pie-slice" style="background-color: #dc2626; width: {{ ($summary['absent_count'] / max($summary['total_records'], 1)) * 100 }}%;">
                <div class="value">{{ $summary['absent_count'] }}</div>
                <div class="label">ABSENT</div>
            </div>
            <div class="pie-slice" style="background-color: #f59e0b; width: {{ ($summary['late_count'] / max($summary['total_records'], 1)) * 100 }}%;">
                <div class="value">{{ $summary['late_count'] }}</div>
                <div class="label">LATE</div>
            </div>
            <div class="pie-slice" style="background-color: #2563eb; width: {{ ($summary['excused_count'] / max($summary['total_records'], 1)) * 100 }}%;">
                <div class="value">{{ $summary['excused_count'] }}</div>
                <div class="label">EXCUSED</div>
            </div>
        </div>
    </div>

    {{-- Performance Indicators --}}
    <div class="section">
        <div class="section-title">Attendance Performance Visualization</div>

        <div class="chart-container">
            <div class="chart-row">
                <div class="chart-label">Present ({{ $summary['present_count'] }} students)</div>
                <div class="chart-bar-bg">
                    <div class="chart-bar" style="width: {{ ($summary['present_count'] / max($summary['total_records'], 1)) * 100 }}%; background-color: #059669;">
                        {{ round(($summary['present_count'] / max($summary['total_records'], 1)) * 100, 1) }}%
                    </div>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-label">Absent ({{ $summary['absent_count'] }} students)</div>
                <div class="chart-bar-bg">
                    <div class="chart-bar" style="width: {{ ($summary['absent_count'] / max($summary['total_records'], 1)) * 100 }}%; background-color: #dc2626;">
                        {{ round(($summary['absent_count'] / max($summary['total_records'], 1)) * 100, 1) }}%
                    </div>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-label">Late ({{ $summary['late_count'] }} students)</div>
                <div class="chart-bar-bg">
                    <div class="chart-bar" style="width: {{ ($summary['late_count'] / max($summary['total_records'], 1)) * 100 }}%; background-color: #f59e0b;">
                        {{ round(($summary['late_count'] / max($summary['total_records'], 1)) * 100, 1) }}%
                    </div>
                </div>
            </div>

            <div class="chart-row">
                <div class="chart-label">Excused ({{ $summary['excused_count'] }} students)</div>
                <div class="chart-bar-bg">
                    <div class="chart-bar" style="width: {{ ($summary['excused_count'] / max($summary['total_records'], 1)) * 100 }}%; background-color: #2563eb;">
                        {{ round(($summary['excused_count'] / max($summary['total_records'], 1)) * 100, 1) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grade-wise Analysis --}}
    @if($attendances->count() > 0)
    <div class="section">
        <div class="section-title">Grade-wise Attendance Analysis</div>

        <table class="stats-table">
            <thead>
                <tr>
                    <th>Grade</th>
                    <th class="text-right">Total Records</th>
                    <th class="text-right">Present</th>
                    <th class="text-right">Absent</th>
                    <th class="text-right">Late</th>
                    <th class="text-right">Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $gradeGroups = $attendances->groupBy(fn($a) => $a->grade->name ?? $a->student->grade->name ?? 'Unassigned');
                @endphp
                @foreach($gradeGroups->sortKeys() as $gradeName => $gradeAttendances)
                    @php
                        $gradeTotal = $gradeAttendances->count();
                        $gradePresent = $gradeAttendances->where('status', 'present')->count();
                        $gradeAbsent = $gradeAttendances->where('status', 'absent')->count();
                        $gradeLate = $gradeAttendances->where('status', 'late')->count();
                        $gradeRate = $gradeTotal > 0 ? round(($gradePresent / $gradeTotal) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $gradeName }}</strong></td>
                        <td class="text-right">{{ $gradeTotal }}</td>
                        <td class="text-right" style="color: #059669;">{{ $gradePresent }}</td>
                        <td class="text-right" style="color: #dc2626;">{{ $gradeAbsent }}</td>
                        <td class="text-right" style="color: #f59e0b;">{{ $gradeLate }}</td>
                        <td class="text-right">
                            <strong style="color: {{ $gradeRate >= 90 ? '#059669' : ($gradeRate >= 75 ? '#f59e0b' : '#dc2626') }};">
                                {{ $gradeRate }}%
                            </strong>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Student Participation --}}
    <div class="section">
        <div class="section-title">Student Participation Overview</div>

        <div style="padding: 15px; background-color: #eff6ff; border: 1px solid #bfdbfe;">
            <p style="margin-bottom: 10px;"><strong>Unique Students Tracked:</strong> {{ $summary['unique_students'] }}</p>
            <p style="margin-bottom: 10px;"><strong>Average Records per Student:</strong> {{ $summary['unique_students'] > 0 ? round($summary['total_records'] / $summary['unique_students'], 1) : 0 }} days</p>
            @if($dateFrom && $dateTo)
                @php
                    $daysDiff = \Carbon\Carbon::parse($dateFrom)->diffInDays(\Carbon\Carbon::parse($dateTo)) + 1;
                @endphp
                <p><strong>Report Period:</strong> {{ $daysDiff }} days</p>
            @endif
        </div>
    </div>

    {{-- Key Insights --}}
    <div class="section">
        <div class="section-title">Key Insights & Recommendations</div>

        @if($attendanceRate >= 90)
            <div class="success-box">
                <strong>Excellent Performance!</strong> The attendance rate of {{ $attendanceRate }}% is exceptional. This indicates strong student engagement and commitment.
            </div>
        @elseif($attendanceRate >= 75)
            <div class="insight-box">
                <strong>Good Performance.</strong> The attendance rate of {{ $attendanceRate }}% is satisfactory. Consider strategies to improve further.
            </div>
        @else
            <div class="danger-box">
                <strong>Needs Attention!</strong> The attendance rate of {{ $attendanceRate }}% requires immediate intervention. Review policies and engage with parents.
            </div>
        @endif

        <div style="margin-top: 15px; padding: 12px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
            <strong style="font-size: 11px; color: #1e40af;">Statistical Summary:</strong>
            <ul style="margin: 8px 0 0 20px; line-height: 1.8; font-size: 9px;">
                <li>{{ $summary['present_count'] }} attendance records marked as present ({{ round(($summary['present_count'] / max($summary['total_records'], 1)) * 100, 1) }}%)</li>
                <li>{{ $summary['absent_count'] }} absences recorded ({{ round(($summary['absent_count'] / max($summary['total_records'], 1)) * 100, 1) }}%)</li>
                <li>{{ $summary['late_count'] }} late arrivals ({{ round(($summary['late_count'] / max($summary['total_records'], 1)) * 100, 1) }}%)</li>
                <li>{{ $summary['excused_count'] }} excused absences ({{ round(($summary['excused_count'] / max($summary['total_records'], 1)) * 100, 1) }}%)</li>
                <li>{{ $summary['unique_students'] }} unique students participated during this period</li>
            </ul>
        </div>
    </div>

    {{-- Recommendations --}}
    <div style="margin-top: 20px; padding: 12px; border: 2px solid #2563eb; background-color: #eff6ff;">
        <strong style="color: #1e40af; font-size: 11px;">Recommended Actions:</strong>
        <ol style="margin: 8px 0 0 20px; line-height: 1.8; font-size: 9px;">
            @if($summary['absent_count'] > 0)
                <li>Follow up with parents of students with {{ $summary['absent_count'] }} recorded absences</li>
            @endif
            @if($summary['late_count'] > 0)
                <li>Review and address chronic lateness - {{ $summary['late_count'] }} late arrivals recorded</li>
            @endif
            @if($attendanceRate < 75)
                <li>Implement attendance improvement program - current rate below target</li>
            @endif
            @if($attendanceRate < 90)
                <li>Schedule parent meetings to discuss importance of regular attendance</li>
            @endif
            <li>Recognize and reward classes/students with excellent attendance</li>
            <li>Review attendance tracking procedures for accuracy and consistency</li>
            <li>Continue monitoring trends and intervene early for at-risk students</li>
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
