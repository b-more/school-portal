<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $schoolName }} - Master Timetable</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7px;
            color: #333;
            line-height: 1.3;
        }

        .page-break {
            page-break-after: always;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #1e3a5f;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }

        .logo-section img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .school-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .school-name {
            color: #1e3a5f;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .school-address {
            color: #666;
            font-size: 8px;
            margin-bottom: 1px;
        }

        .document-title {
            color: #1e3a5f;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .document-subtitle {
            color: #666;
            font-size: 9px;
            margin-top: 2px;
        }

        .day-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2d4a6f 100%);
            color: white;
            padding: 8px 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
        }

        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .timetable-grid th {
            background-color: #1e3a5f;
            color: white;
            padding: 5px 3px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border: 1px solid #1e3a5f;
        }

        .timetable-grid th:first-child {
            text-align: left;
            padding-left: 6px;
            width: 70px;
        }

        .timetable-grid td {
            padding: 4px 2px;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: middle;
            height: 38px;
            font-size: 6.5px;
        }

        .timetable-grid tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .period-cell {
            background-color: #f3f4f6 !important;
            font-weight: bold;
            text-align: left;
            padding-left: 5px !important;
        }

        .period-name {
            color: #1e3a5f;
            font-size: 7px;
        }

        .period-time {
            color: #6b7280;
            font-size: 6px;
            white-space: nowrap;
        }

        .entry-cell {
            min-width: 50px;
        }

        .subject-name {
            font-weight: bold;
            font-size: 6.5px;
            color: #1e3a5f;
            margin-bottom: 1px;
            line-height: 1.2;
        }

        .teacher-name {
            font-size: 6px;
            color: #6b7280;
            line-height: 1.2;
        }

        .break-row {
            background-color: #fef3c7 !important;
        }

        .break-row td {
            background-color: #fef3c7 !important;
        }

        .break-cell {
            color: #92400e;
            font-style: italic;
            font-size: 7px;
            font-weight: 500;
        }

        .empty-cell {
            color: #d1d5db;
            font-size: 6px;
        }

        .class-header {
            font-size: 7px;
            line-height: 1.3;
        }

        .class-grade {
            font-weight: bold;
        }

        .class-section {
            font-weight: normal;
            opacity: 0.9;
        }

        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7px;
            color: #666;
        }

        .footer-note {
            margin-top: 3px;
            font-style: italic;
        }

        .legend {
            margin-top: 8px;
            padding: 6px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-size: 7px;
        }

        .legend-title {
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 3px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 12px;
        }

        .legend-color {
            display: inline-block;
            width: 10px;
            height: 10px;
            vertical-align: middle;
            margin-right: 3px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    @foreach($days as $dayIndex => $day)
        {{-- Header --}}
        <div class="header">
            <div class="header-content">
                @if($schoolLogo && file_exists($schoolLogo))
                    <div class="logo-section">
                        <img src="{{ $schoolLogo }}" alt="School Logo">
                    </div>
                @endif
                <div class="school-info">
                    <div class="school-name">{{ $schoolName }}</div>
                    @if($schoolAddress)
                        <div class="school-address">{{ $schoolAddress }}</div>
                    @endif
                    <div class="document-title">MASTER TIMETABLE</div>
                    <div class="document-subtitle">Academic Year: {{ $academicYear->name }}</div>
                </div>
                @if($schoolLogo && file_exists($schoolLogo))
                    <div class="logo-section"></div>
                @endif
            </div>
        </div>

        {{-- Day Header --}}
        <div class="day-header">
            {{ strtoupper($day) }}
        </div>

        {{-- Timetable Grid --}}
        <table class="timetable-grid">
            <thead>
                <tr>
                    <th>Period</th>
                    @foreach($classSections as $classSection)
                        <th>
                            <div class="class-header">
                                <span class="class-grade">{{ $classSection->grade?->name ?? 'N/A' }}</span><br>
                                <span class="class-section">{{ $classSection->name }}</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                    <tr class="{{ $period->isBreak() ? 'break-row' : '' }}">
                        {{-- Period Info --}}
                        <td class="period-cell">
                            <div class="period-name">{{ $period->short_name ?? $period->name }}</div>
                            <div class="period-time">
                                {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                            </div>
                        </td>

                        {{-- Class Cells --}}
                        @foreach($classSections as $classSection)
                            <td class="entry-cell">
                                @if($period->isBreak())
                                    <span class="break-cell">
                                        @switch($period->type)
                                            @case('assembly')
                                                Assembly
                                                @break
                                            @case('tea_break')
                                                Tea Break
                                                @break
                                            @case('lunch_break')
                                                Lunch
                                                @break
                                            @default
                                                Break
                                        @endswitch
                                    </span>
                                @else
                                    @php
                                        $entry = $timetableData[$classSection->id][$period->id][$day] ?? null;
                                    @endphp
                                    @if($entry)
                                        <div class="subject-name">{{ $entry->subject?->short_code ?? $entry->subject?->name ?? '-' }}</div>
                                        <div class="teacher-name">{{ $entry->teacher ? Str::limit($entry->teacher->name, 12) : '-' }}</div>
                                    @else
                                        <span class="empty-cell">-</span>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Legend (only on first page) --}}
        @if($dayIndex === 0)
            <div class="legend">
                <span class="legend-title">Legend:</span>
                <span class="legend-item">
                    <span class="legend-color" style="background-color: #fef3c7; border: 1px solid #fcd34d;"></span>
                    Break
                </span>
                <span class="legend-item">
                    <span class="legend-color" style="background-color: #f9fafb; border: 1px solid #e5e7eb;"></span>
                    Lesson
                </span>
            </div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div>{{ $schoolName }} | Generated on {{ $generatedAt }}</div>
            <div class="footer-note">This is a computer-generated document.</div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
