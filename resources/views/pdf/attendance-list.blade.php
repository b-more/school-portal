<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Attendance List</title>
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
        .period-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 10px;
        }
        .period-box strong {
            color: #1e40af;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .summary-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
        }
        .summary-value.present {
            color: #059669;
        }
        .summary-value.absent {
            color: #dc2626;
        }
        .summary-value.late {
            color: #f59e0b;
        }
        .summary-value.excused {
            color: #2563eb;
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
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Attendance Records Report</h2>
        <p>Generated on {{ $reportDate }}</p>
    </div>

    {{-- Date Period --}}
    @if($dateFrom || $dateTo)
    <div class="period-box">
        <strong>Report Period:</strong>
        @if($dateFrom && $dateTo)
            {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        @elseif($dateFrom)
            From {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }}
        @elseif($dateTo)
            Up to {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
        @endif
    </div>
    @endif

    {{-- Summary Statistics --}}
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-label">Total Records</div>
            <div class="summary-value">{{ $attendances->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Present</div>
            <div class="summary-value present">{{ $attendances->where('status', 'present')->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Absent</div>
            <div class="summary-value absent">{{ $attendances->where('status', 'absent')->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Late</div>
            <div class="summary-value late">{{ $attendances->where('status', 'late')->count() }}</div>
        </div>
    </div>

    {{-- Attendance Rate --}}
    @php
        $totalRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;
    @endphp
    <div style="text-align: center; padding: 10px; background-color: {{ $attendanceRate >= 90 ? '#d1fae5' : ($attendanceRate >= 75 ? '#fef3c7' : '#fee2e2') }}; margin-bottom: 15px; border-radius: 5px;">
        <strong style="font-size: 11px;">Overall Attendance Rate: {{ $attendanceRate }}%</strong>
        @if($attendanceRate >= 90)
            <span class="badge badge-success">Excellent</span>
        @elseif($attendanceRate >= 75)
            <span class="badge badge-warning">Good</span>
        @else
            <span class="badge badge-danger">Needs Improvement</span>
        @endif
    </div>

    {{-- Attendance Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 10%;">Student ID</th>
                <th style="width: 20%;">Student Name</th>
                <th style="width: 12%;">Grade</th>
                <th style="width: 10%;">Class</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Check In</th>
                <th style="width: 8%;">Check Out</th>
                <th style="width: 12%;">Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $index => $attendance)
            <tr>
                <td>{{ $attendance->attendance_date->format('d/m/Y') }}</td>
                <td>{{ $attendance->student->student_id_number }}</td>
                <td>{{ $attendance->student->name }}</td>
                <td>{{ $attendance->grade->name ?? $attendance->student->grade->name ?? 'N/A' }}</td>
                <td>{{ $attendance->classSection->name ?? $attendance->student->classSection->name ?? 'N/A' }}</td>
                <td>
                    @if($attendance->status === 'present')
                        <span class="badge badge-success">Present</span>
                    @elseif($attendance->status === 'absent')
                        <span class="badge badge-danger">Absent</span>
                    @elseif($attendance->status === 'late')
                        <span class="badge badge-warning">Late</span>
                    @else
                        <span class="badge badge-info">Excused</span>
                    @endif
                </td>
                <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-' }}</td>
                <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '-' }}</td>
                <td style="font-size: 7px;">{{ $attendance->notes ? substr($attendance->notes, 0, 30) . (strlen($attendance->notes) > 30 ? '...' : '') : '-' }}</td>
            </tr>
            @if(($index + 1) % 30 === 0 && $index + 1 < $attendances->count())
                </tbody>
            </table>
            <div class="page-break"></div>

            {{-- Repeat header on new page --}}
            <div class="header">
                <h1>{{ $schoolName }}</h1>
                <h2>Attendance Records Report (Continued)</h2>
                <p>Page {{ ceil(($index + 1) / 30) + 1 }}</p>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 10%;">Student ID</th>
                        <th style="width: 20%;">Student Name</th>
                        <th style="width: 12%;">Grade</th>
                        <th style="width: 10%;">Class</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 8%;">Check In</th>
                        <th style="width: 8%;">Check Out</th>
                        <th style="width: 12%;">Notes</th>
                    </tr>
                </thead>
                <tbody>
            @endif
            @endforeach
        </tbody>
    </table>

    {{-- Student-wise Summary --}}
    @if($attendances->count() > 0)
    <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 10px; color: #1e40af; margin-bottom: 8px;">Top 10 Students by Attendance Records</h3>
        <table style="width: 100%; font-size: 8px; border-collapse: collapse;">
            <tr style="background-color: #e5e7eb;">
                <th style="padding: 5px; text-align: left;">Student</th>
                <th style="padding: 5px; text-align: center;">Total Days</th>
                <th style="padding: 5px; text-align: center;">Present</th>
                <th style="padding: 5px; text-align: center;">Absent</th>
                <th style="padding: 5px; text-align: center;">Late</th>
                <th style="padding: 5px; text-align: center;">Rate</th>
            </tr>
            @php
                $studentGroups = $attendances->groupBy('student_id')->sortByDesc(function($records) {
                    return $records->count();
                })->take(10);
            @endphp
            @foreach($studentGroups as $studentId => $records)
                @php
                    $student = $records->first()->student;
                    $total = $records->count();
                    $present = $records->where('status', 'present')->count();
                    $absent = $records->where('status', 'absent')->count();
                    $late = $records->where('status', 'late')->count();
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                @endphp
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 5px;">{{ $student->name }} ({{ $student->student_id_number }})</td>
                    <td style="padding: 5px; text-align: center;">{{ $total }}</td>
                    <td style="padding: 5px; text-align: center; color: #059669;">{{ $present }}</td>
                    <td style="padding: 5px; text-align: center; color: #dc2626;">{{ $absent }}</td>
                    <td style="padding: 5px; text-align: center; color: #f59e0b;">{{ $late }}</td>
                    <td style="padding: 5px; text-align: center;"><strong>{{ $rate }}%</strong></td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Daily Breakdown --}}
    @if($attendances->count() > 0)
    <div style="margin-top: 15px; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 10px; color: #1e40af; margin-bottom: 8px;">Daily Attendance Breakdown</h3>
        <table style="width: 100%; font-size: 8px; border-collapse: collapse;">
            <tr style="background-color: #e5e7eb;">
                <th style="padding: 5px; text-align: left;">Date</th>
                <th style="padding: 5px; text-align: center;">Total</th>
                <th style="padding: 5px; text-align: center;">Present</th>
                <th style="padding: 5px; text-align: center;">Absent</th>
                <th style="padding: 5px; text-align: center;">Late</th>
                <th style="padding: 5px; text-align: center;">Excused</th>
                <th style="padding: 5px; text-align: center;">Rate</th>
            </tr>
            @php
                $dateGroups = $attendances->groupBy(fn($a) => $a->attendance_date->format('Y-m-d'))->sortKeysDesc()->take(15);
            @endphp
            @foreach($dateGroups as $date => $records)
                @php
                    $total = $records->count();
                    $present = $records->where('status', 'present')->count();
                    $absent = $records->where('status', 'absent')->count();
                    $late = $records->where('status', 'late')->count();
                    $excused = $records->where('status', 'excused')->count();
                    $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                @endphp
                <tr style="border-bottom: 1px solid #e5e7eb;">
                    <td style="padding: 5px;">{{ \Carbon\Carbon::parse($date)->format('D, M d, Y') }}</td>
                    <td style="padding: 5px; text-align: center;">{{ $total }}</td>
                    <td style="padding: 5px; text-align: center; color: #059669;">{{ $present }}</td>
                    <td style="padding: 5px; text-align: center; color: #dc2626;">{{ $absent }}</td>
                    <td style="padding: 5px; text-align: center; color: #f59e0b;">{{ $late }}</td>
                    <td style="padding: 5px; text-align: center; color: #2563eb;">{{ $excused }}</td>
                    <td style="padding: 5px; text-align: center;">
                        <strong style="color: {{ $rate >= 90 ? '#059669' : ($rate >= 75 ? '#f59e0b' : '#dc2626') }};">{{ $rate }}%</strong>
                    </td>
                </tr>
            @endforeach
        </table>
        @if($dateGroups->count() >= 15)
            <p style="margin-top: 5px; font-size: 7px; color: #6b7280; font-style: italic;">Showing last 15 days only</p>
        @endif
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $schoolName }} | Generated on {{ $reportDate }}</p>
        <p>Total Records: {{ $attendances->count() }} | Overall Attendance Rate: {{ $attendanceRate }}%</p>
        <p>This is a computer-generated document.</p>
    </div>
</body>
</html>
