<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termly Attendance Register - {{ $className }} - {{ $term->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }

        .container {
            width: 100%;
        }

        /* ========== HEADER ========== */
        .header {
            border: 1.5px solid #000;
            padding: 4px 8px;
            margin-bottom: 0;
        }

        .header-inner {
            display: table;
            width: 100%;
        }

        .header-logo-cell {
            display: table-cell;
            width: 40px;
            vertical-align: middle;
        }

        .header-logo-cell img {
            width: 35px;
            height: 35px;
        }

        .header-text-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding-left: 4px;
            padding-right: 40px;
        }

        .school-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000;
        }

        .school-motto {
            font-size: 6px;
            font-style: italic;
            color: #333;
            margin: 1px 0 2px 0;
        }

        .school-address {
            font-size: 5.5px;
            color: #000;
            line-height: 1.4;
        }

        /* ========== TITLE BAR ========== */
        .report-title {
            border: 1.5px solid #000;
            border-top: none;
            padding: 3px 0;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #000;
        }

        /* ========== INFO ROW ========== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-left: 1.5px solid #000;
            border-right: 1.5px solid #000;
        }

        .info-table td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 2px 5px;
            font-size: 6.5px;
        }

        .info-table td:last-child {
            border-right: none;
        }

        .info-table .label {
            font-weight: bold;
            font-size: 6px;
            text-transform: uppercase;
            color: #000;
            width: 10%;
        }

        .info-table .value {
            font-weight: 600;
            color: #000;
        }

        /* ========== PAGE SUBTITLE ========== */
        .page-subtitle {
            border: 1.5px solid #000;
            border-top: none;
            padding: 3px 0;
            text-align: center;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1e3a5f;
            background: #e8eef5;
        }

        /* ========== PAGE BREAK ========== */
        .page-break {
            page-break-after: always;
        }

        /* ================================================
           PAGE 1 — STUDENT ROSTER
           ================================================ */
        table.roster {
            width: 100%;
            border-collapse: collapse;
        }

        table.roster th {
            background: #1e3a5f;
            color: #ffffff;
            font-size: 7px;
            font-weight: 700;
            padding: 4px 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e3a5f;
            text-align: left;
        }

        table.roster th.col-center {
            text-align: center;
        }

        table.roster td {
            font-size: 8px;
            padding: 5px 6px;
            border: 0.5px solid #999;
            vertical-align: middle;
            word-wrap: break-word;
        }

        table.roster td.center {
            text-align: center;
        }

        table.roster td.num-cell {
            text-align: center;
            color: #666;
            font-size: 7.5px;
            width: 30px;
        }

        table.roster .col-gender { width: 60px; }

        table.roster tbody tr:nth-child(even) {
            background: #f7f9fc;
        }

        /* ================================================
           PAGE 2 — ATTENDANCE GRID
           ================================================ */
        table.register {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.register th,
        table.register td {
            border: 0.5px solid #555;
            text-align: center;
            vertical-align: middle;
        }

        /* Fixed columns */
        table.register .col-num { width: 14px; }
        table.register .col-name { width: 110px; text-align: left; }
        table.register .col-total { width: 13px; }

        /* Week header row */
        table.register th.week-header {
            background: #1e3a5f;
            color: #ffffff;
            font-size: 5.5px;
            font-weight: 700;
            padding: 2px 0;
            letter-spacing: 0.3px;
        }

        /* Day sub-header */
        table.register th.day-header {
            background: #e8eef5;
            color: #1e3a5f;
            font-size: 6px;
            font-weight: 700;
            padding: 1.5px 0;
        }

        /* Info column headers */
        table.register th.info-header {
            background: #f0f0f0;
            color: #000;
            font-size: 5.5px;
            font-weight: 700;
            padding: 2px 1px;
            text-transform: uppercase;
        }

        /* Total column headers */
        table.register th.total-header {
            background: #f0f0f0;
            color: #000;
            font-size: 5.5px;
            font-weight: 700;
            padding: 2px 0;
        }

        table.register th.total-header-p { color: #065f46; }
        table.register th.total-header-x { color: #991b1b; }
        table.register th.total-header-s { color: #1e40af; }
        table.register th.total-header-y { color: #92400e; }
        table.register th.total-header-l { color: #5b21b6; }

        /* Body cells */
        table.register td {
            font-size: 6.5px;
            padding: 2px 0;
        }

        table.register td.name-cell {
            text-align: left;
            padding-left: 2px;
            font-weight: 500;
            font-size: 6px;
            word-wrap: break-word;
        }

        table.register td.num-cell {
            color: #666;
            font-size: 5.5px;
        }

        /* Status colors in cells */
        .sym-P { color: #065f46; font-weight: 700; }
        .sym-X { color: #991b1b; font-weight: 700; }
        .sym-S { color: #1e40af; font-weight: 700; }
        .sym-Y { color: #92400e; font-weight: 700; }
        .sym-L { color: #5b21b6; font-weight: 700; }
        .sym-dash { color: #bbb; }

        /* Total cells */
        td.total-p { font-weight: 700; color: #065f46; }
        td.total-x { font-weight: 700; color: #991b1b; }
        td.total-s { font-weight: 700; color: #1e40af; }
        td.total-y { font-weight: 700; color: #92400e; }
        td.total-l { font-weight: 700; color: #5b21b6; }

        /* Alternating rows */
        table.register tbody tr:nth-child(even) {
            background: #fafafa;
        }

        /* Totals row */
        table.register tr.totals-row td {
            background: #f0f0f0;
            font-weight: 700;
            font-size: 6px;
            border-top: 1.5px solid #000;
        }

        /* ========== LEGEND ========== */
        .legend {
            margin-top: 3px;
            padding: 3px 0;
            font-size: 6px;
        }

        .legend-items {
            display: inline;
        }

        .legend-item {
            margin-right: 10px;
            font-weight: 600;
        }

        .legend .sym-label {
            font-weight: 800;
            margin-right: 1px;
        }

        /* ========== FOOTER ========== */
        .footer {
            margin-top: 3px;
            padding: 3px 0;
            font-size: 5.5px;
            color: #000;
            border-top: 0.5px solid #ccc;
        }

        .footer-inner {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
            vertical-align: middle;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        .footer .bold {
            font-weight: bold;
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

    {{-- ========================================================
         Shared PHP: prepare address/contact parts once
         ======================================================== --}}
    @php
        $addressParts = array_filter([
            $schoolSettings->address ?? null,
            $schoolSettings->city ?? null,
            $schoolSettings->state_province ?? null,
            $schoolSettings->country ?? null,
        ]);
        $contactParts = array_filter([
            ($schoolSettings->phone ?? null) ? 'Tel: ' . $schoolSettings->phone : null,
            ($schoolSettings->email ?? null) ? 'Email: ' . $schoolSettings->email : null,
        ]);
    @endphp

    {{-- ========================================================
         PAGE 1 — STUDENT INFORMATION
         ======================================================== --}}
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-inner">
                <div class="header-logo-cell">
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo">
                </div>
                <div class="header-text-cell">
                    <div class="school-name">{{ $schoolSettings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                    @if($schoolSettings->school_motto)
                        <div class="school-motto">"{{ $schoolSettings->school_motto }}"</div>
                    @endif
                    <div class="school-address">
                        @if(!empty($addressParts))
                            {{ implode(', ', $addressParts) }}
                            @if($schoolSettings->postal_code) &middot; P.O. Box {{ $schoolSettings->postal_code }}@endif
                        @endif
                        @if(!empty($contactParts))
                            &nbsp;|&nbsp; {{ implode('  |  ', $contactParts) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="report-title">Termly Attendance Register</div>

        <!-- Info Row -->
        <table class="info-table">
            <tr>
                <td class="label">Term</td>
                <td class="value" style="width: 25%;">{{ $term->name }}</td>
                <td class="label">Class</td>
                <td class="value" style="width: 25%;">{{ $className }}</td>
                <td class="label">Grade Teacher</td>
                <td class="value" style="width: 20%;">{{ $gradeTeacher }}</td>
                <td class="label">Students</td>
                <td class="value" style="width: 5%;">{{ $totalStudents }}</td>
            </tr>
        </table>

        <!-- Subtitle -->
        <div class="page-subtitle">Student Information</div>

        <!-- Student Roster Table -->
        <table class="roster">
            <thead>
                <tr>
                    <th class="col-center" style="width: 30px;">#</th>
                    <th>Full Name</th>
                    <th class="col-gender col-center">Gender</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr>
                        <td class="num-cell">{{ $index + 1 }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td class="center">{{ $student['gender'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-inner">
                <div class="footer-left">
                    <span class="bold">Generated:</span> {{ $generatedAt->format('d F Y, H:i') }}
                </div>
                <div class="footer-right">
                    {{ $schoolSettings->school_name ?? 'School' }} &middot; {{ $className }} &middot; {{ $term->name }}
                </div>
            </div>
        </div>

    </div>

    {{-- Page Break --}}
    <div class="page-break"></div>

    {{-- ========================================================
         PAGE 2 — ATTENDANCE RECORD
         ======================================================== --}}
    <div class="container">

        <!-- Header (repeated) -->
        <div class="header">
            <div class="header-inner">
                <div class="header-logo-cell">
                    <img src="{{ public_path('images/logo.png') }}" alt="Logo">
                </div>
                <div class="header-text-cell">
                    <div class="school-name">{{ $schoolSettings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                    @if($schoolSettings->school_motto)
                        <div class="school-motto">"{{ $schoolSettings->school_motto }}"</div>
                    @endif
                    <div class="school-address">
                        @if(!empty($addressParts))
                            {{ implode(', ', $addressParts) }}
                            @if($schoolSettings->postal_code) &middot; P.O. Box {{ $schoolSettings->postal_code }}@endif
                        @endif
                        @if(!empty($contactParts))
                            &nbsp;|&nbsp; {{ implode('  |  ', $contactParts) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="report-title">Termly Attendance Register</div>

        <!-- Info Row -->
        <table class="info-table">
            <tr>
                <td class="label">Term</td>
                <td class="value" style="width: 25%;">{{ $term->name }}</td>
                <td class="label">Class</td>
                <td class="value" style="width: 25%;">{{ $className }}</td>
                <td class="label">Grade Teacher</td>
                <td class="value" style="width: 20%;">{{ $gradeTeacher }}</td>
                <td class="label">Students</td>
                <td class="value" style="width: 5%;">{{ $totalStudents }}</td>
            </tr>
        </table>

        <!-- Subtitle -->
        <div class="page-subtitle">Attendance Record</div>

        <!-- Attendance Grid Table -->
        <table class="register">
            <thead>
                <!-- Row 1: Week groups -->
                <tr>
                    <th class="info-header col-num" rowspan="2">#</th>
                    <th class="info-header col-name" rowspan="2">Name</th>
                    @for($w = 0; $w < 13; $w++)
                        <th class="week-header" colspan="5">W{{ $w + 1 }}</th>
                    @endfor
                    <th class="total-header total-header-p col-total" rowspan="2">P</th>
                    <th class="total-header total-header-x col-total" rowspan="2">X</th>
                    <th class="total-header total-header-s col-total" rowspan="2">S</th>
                    <th class="total-header total-header-y col-total" rowspan="2">Y</th>
                    <th class="total-header total-header-l col-total" rowspan="2">L</th>
                </tr>
                <!-- Row 2: Day sub-headers (M T W T F × 13) -->
                <tr>
                    @for($w = 0; $w < 13; $w++)
                        <th class="day-header">M</th>
                        <th class="day-header">T</th>
                        <th class="day-header">W</th>
                        <th class="day-header">T</th>
                        <th class="day-header">F</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                    <tr>
                        <td class="num-cell">{{ $index + 1 }}</td>
                        <td class="name-cell">{{ $student['name'] }}</td>
                        @foreach($weeks as $weekDays)
                            @foreach($weekDays as $date)
                                @php
                                    $sym = $student['days'][$date->format('Y-m-d')] ?? '-';
                                    $symClass = $sym === '-' ? 'sym-dash' : 'sym-' . $sym;
                                @endphp
                                <td class="{{ $symClass }}">{{ $sym }}</td>
                            @endforeach
                        @endforeach
                        <td class="total-p">{{ $student['totals']['P'] ?: '' }}</td>
                        <td class="total-x">{{ $student['totals']['X'] ?: '' }}</td>
                        <td class="total-s">{{ $student['totals']['S'] ?: '' }}</td>
                        <td class="total-y">{{ $student['totals']['Y'] ?: '' }}</td>
                        <td class="total-l">{{ $student['totals']['L'] ?: '' }}</td>
                    </tr>
                @endforeach

                <!-- Totals Row -->
                <tr class="totals-row">
                    <td colspan="2" style="text-align: right; padding-right: 4px; font-size: 5.5px;">DAILY TOTAL</td>
                    @foreach($weeks as $weekDays)
                        @foreach($weekDays as $date)
                            @php
                                $dateStr = $date->format('Y-m-d');
                                $dayCount = 0;
                                foreach ($students as $s) {
                                    $sym = $s['days'][$dateStr] ?? '-';
                                    if ($sym !== '-') $dayCount++;
                                }
                            @endphp
                            <td>{{ $dayCount ?: '' }}</td>
                        @endforeach
                    @endforeach
                    @php
                        $grandP = $grandX = $grandS = $grandY = $grandL = 0;
                        foreach ($students as $s) {
                            $grandP += $s['totals']['P'];
                            $grandX += $s['totals']['X'];
                            $grandS += $s['totals']['S'];
                            $grandY += $s['totals']['Y'];
                            $grandL += $s['totals']['L'];
                        }
                    @endphp
                    <td class="total-p">{{ $grandP ?: '' }}</td>
                    <td class="total-x">{{ $grandX ?: '' }}</td>
                    <td class="total-s">{{ $grandS ?: '' }}</td>
                    <td class="total-y">{{ $grandY ?: '' }}</td>
                    <td class="total-l">{{ $grandL ?: '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Legend -->
        <div class="legend">
            <span class="legend-items">
                <span class="legend-item"><span class="legend sym-label sym-P">P</span>=Present</span>
                <span class="legend-item"><span class="legend sym-label sym-X">X</span>=Absent</span>
                <span class="legend-item"><span class="legend sym-label sym-S">S</span>=Sick</span>
                <span class="legend-item"><span class="legend sym-label sym-Y">Y</span>=Late</span>
                <span class="legend-item"><span class="legend sym-label sym-L">L</span>=Excused</span>
                <span class="legend-item"><span class="legend sym-label sym-dash">-</span>=Not Marked</span>
            </span>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-inner">
                <div class="footer-left">
                    <span class="bold">Generated:</span> {{ $generatedAt->format('d F Y, H:i') }}
                </div>
                <div class="footer-right">
                    {{ $schoolSettings->school_name ?? 'School' }} &middot; {{ $className }} &middot; {{ $term->name }}
                </div>
            </div>
        </div>

    </div>

</body>
</html>
