<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Register - {{ $month }} {{ $year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.3;
        }

        /* Header with Logo */
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 3px double #1e40af;
        }
        .header-content {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .logo-section {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        .school-logo {
            width: 55px;
            height: 55px;
        }
        .school-info-section {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .school-name {
            color: #1e40af;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .school-motto {
            font-style: italic;
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 4px;
        }
        .school-contact {
            font-size: 9px;
            color: #555;
        }
        .document-title {
            background: #1e40af;
            color: white;
            padding: 6px 25px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 8px auto;
            display: inline-block;
        }
        .class-info {
            font-size: 12px;
            color: #1e40af;
            font-weight: bold;
            margin-top: 5px;
        }
        .date-info {
            font-size: 9px;
            color: #6b7280;
            margin-top: 3px;
        }

        /* Register Table */
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
        }
        .register-table th,
        .register-table td {
            border: 1px solid #c0c0c0;
            padding: 3px 2px;
            text-align: center;
        }
        .register-table th {
            background: #1e40af;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }
        .register-table th.date-header {
            width: 20px;
            min-width: 20px;
        }
        .register-table th.name-header {
            width: 130px;
            min-width: 130px;
            text-align: left;
            padding-left: 6px;
        }
        .register-table th.num-header {
            width: 22px;
        }
        .register-table th.total-header {
            width: 24px;
            background: #374151;
        }
        .register-table td.student-num {
            font-weight: bold;
            color: #555;
            font-size: 9px;
        }
        .register-table td.student-name {
            text-align: left;
            padding-left: 6px;
            font-weight: 500;
            font-size: 9px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 130px;
        }
        .register-table tr:nth-child(even) {
            background: #f5f5f5;
        }

        /* Status cells */
        .status-cell {
            font-weight: bold;
            font-size: 9px;
        }
        .status-cell.p {
            background: #d1fae5;
            color: #065f46;
        }
        .status-cell.a {
            background: #fee2e2;
            color: #991b1b;
        }
        .status-cell.l {
            background: #fef3c7;
            color: #92400e;
        }
        .status-cell.s {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-cell.empty {
            color: #c0c0c0;
        }

        /* Total columns */
        .total-cell {
            font-weight: bold;
            font-size: 9px;
        }
        .total-cell.p { color: #065f46; }
        .total-cell.a { color: #991b1b; }
        .total-cell.l { color: #92400e; }
        .total-cell.s { color: #1e40af; }

        /* Weekend indicator */
        .weekend {
            background: #e0e0e0 !important;
        }

        /* Footer Section - Legend + Summary in one row */
        .footer-section {
            margin-top: 10px;
            padding: 8px 10px;
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
        }
        .footer-row {
            display: table;
            width: 100%;
        }
        .legend-section {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }
        .summary-section {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: right;
        }
        .legend-inline {
            font-size: 10px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 15px;
        }
        .legend-box {
            display: inline-block;
            width: 16px;
            height: 14px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            border-radius: 2px;
            margin-right: 3px;
            vertical-align: middle;
        }
        .legend-box.p { background: #d1fae5; color: #065f46; }
        .legend-box.a { background: #fee2e2; color: #991b1b; }
        .legend-box.l { background: #fef3c7; color: #92400e; }
        .legend-box.s { background: #dbeafe; color: #1e40af; }
        .legend-text {
            font-size: 9px;
            color: #555;
        }
        .summary-text {
            font-size: 10px;
            color: #333;
        }
        .summary-text strong {
            color: #1e40af;
        }

        /* Signature Lines */
        .signature-section {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            text-align: center;
            width: 33%;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 35px;
            padding-top: 5px;
            font-size: 10px;
            color: #555;
        }

        /* Page Footer */
        .page-footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #e0e0e0;
            font-size: 8px;
            color: #888;
            display: table;
            width: 100%;
        }
        .page-footer-left {
            display: table-cell;
            text-align: left;
        }
        .page-footer-center {
            display: table-cell;
            text-align: center;
        }
        .page-footer-right {
            display: table-cell;
            text-align: right;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    {{-- Header with Logo --}}
    <div class="header">
        <div class="header-content">
            <div class="logo-section">
                @if($schoolLogo)
                    <img src="{{ public_path('storage/' . $schoolLogo) }}" class="school-logo" alt="Logo">
                @else
                    <img src="{{ public_path('images/logo.png') }}" class="school-logo" alt="Logo">
                @endif
            </div>
            <div class="school-info-section">
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-contact">
                    {{ $schoolSettings->address ?? '' }}{{ ($schoolSettings->city ?? null) ? ', ' . $schoolSettings->city : '' }}{{ ($schoolSettings->country ?? null) ? ', ' . $schoolSettings->country : '' }}
                    <br>
                    {{ ($schoolSettings->phone ?? null) ? 'Phone: ' . $schoolSettings->phone : '' }}{{ ($schoolSettings->email ?? null) ? ' | Email: ' . $schoolSettings->email : '' }}
                </div>
            </div>
            <div class="logo-section"></div>
        </div>

        <div class="document-title">Monthly Attendance Register</div>

        <div class="class-info">{{ $classSection->grade->name ?? '' }} - {{ $classSection->name }}</div>
        <div class="date-info">{{ $month }} {{ $year }} | Generated: {{ $reportDate }}</div>
    </div>

    {{-- Attendance Register Table --}}
    <table class="register-table">
        <thead>
            <tr>
                <th class="num-header">#</th>
                <th class="name-header">Student Name</th>
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $date = \Carbon\Carbon::create($year, $startDate->month, $day);
                        $isWeekend = $date->isWeekend();
                    @endphp
                    <th class="date-header {{ $isWeekend ? 'weekend' : '' }}">{{ $day }}</th>
                @endfor
                <th class="total-header">P</th>
                <th class="total-header">A</th>
                <th class="total-header">L</th>
                <th class="total-header">S</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td class="student-num">{{ $index + 1 }}</td>
                    <td class="student-name" title="{{ $student['name'] }}">{{ \Illuminate\Support\Str::limit($student['name'], 22) }}</td>
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $status = $student['days'][$day] ?? '-';
                            $statusClass = strtolower($status);
                            $date = \Carbon\Carbon::create($year, $startDate->month, $day);
                            $isWeekend = $date->isWeekend();
                        @endphp
                        <td class="status-cell {{ $statusClass === '-' ? 'empty' : $statusClass }} {{ $isWeekend ? 'weekend' : '' }}">{{ $status }}</td>
                    @endfor
                    <td class="total-cell p">{{ $student['present'] }}</td>
                    <td class="total-cell a">{{ $student['absent'] }}</td>
                    <td class="total-cell l">{{ $student['late'] }}</td>
                    <td class="total-cell s">{{ $student['sick'] }}</td>
                </tr>
            @endforeach

            {{-- Totals Row --}}
            @php
                $totalP = collect($students)->sum('present');
                $totalA = collect($students)->sum('absent');
                $totalL = collect($students)->sum('late');
                $totalS = collect($students)->sum('sick');
            @endphp
            <tr style="background: #e5e7eb; font-weight: bold;">
                <td colspan="2" style="text-align: right; padding-right: 8px; font-size: 9px;">DAILY TOTAL</td>
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dayTotal = collect($students)->filter(fn($s) => isset($s['days'][$day]) && $s['days'][$day] !== '-')->count();
                    @endphp
                    <td style="font-size: 8px;">{{ $dayTotal > 0 ? $dayTotal : '-' }}</td>
                @endfor
                <td class="total-cell p">{{ $totalP }}</td>
                <td class="total-cell a">{{ $totalA }}</td>
                <td class="total-cell l">{{ $totalL }}</td>
                <td class="total-cell s">{{ $totalS }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Legend and Summary in one row --}}
    <div class="footer-section">
        <div class="footer-row">
            <div class="legend-section">
                <div class="legend-inline">
                    <span class="legend-item"><span class="legend-box p">P</span><span class="legend-text">Present</span></span>
                    <span class="legend-item"><span class="legend-box a">A</span><span class="legend-text">Absent</span></span>
                    <span class="legend-item"><span class="legend-box l">L</span><span class="legend-text">Late</span></span>
                    <span class="legend-item"><span class="legend-box s">S</span><span class="legend-text">Sick/Excused</span></span>
                    <span class="legend-item"><span style="color: #999; font-weight: bold;">-</span> <span class="legend-text">Not Marked</span></span>
                </div>
            </div>
            <div class="summary-section">
                <span class="summary-text">
                    Students: <strong>{{ count($students) }}</strong> |
                    Present: <strong>{{ $totalP }}</strong> |
                    Absent: <strong>{{ $totalA }}</strong> |
                    Late: <strong>{{ $totalL }}</strong> |
                    @php
                        $totalRecords = $totalP + $totalA + $totalL + $totalS;
                        $attendanceRate = $totalRecords > 0 ? round(($totalP / $totalRecords) * 100, 1) : 0;
                    @endphp
                    Attendance Rate: <strong>{{ $attendanceRate }}%</strong>
                </span>
            </div>
        </div>
    </div>

    {{-- Signature Lines --}}
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Class Teacher</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Head Teacher</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Date</div>
        </div>
    </div>

    {{-- Page Footer --}}
    <div class="page-footer">
        <div class="page-footer-left">{{ $schoolName }}</div>
        <div class="page-footer-center">{{ $classSection->grade->name ?? '' }} - {{ $classSection->name }} | {{ $month }} {{ $year }}</div>
        <div class="page-footer-right">Page 1</div>
    </div>
</body>
</html>
