<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $schoolName }} - Class Timetable</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }

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
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #1e3a5f;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }

        .logo-section img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .school-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .school-name {
            color: #1e3a5f;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .school-address {
            color: #666;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .document-title {
            color: #1e3a5f;
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
        }

        .document-subtitle {
            color: #666;
            font-size: 10px;
            margin-top: 3px;
        }

        .class-info {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f0fa 100%);
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
            text-align: center;
        }

        .class-name {
            color: #1e3a5f;
            font-size: 16px;
            font-weight: bold;
        }

        .class-teacher {
            color: #666;
            font-size: 10px;
            margin-top: 4px;
        }

        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .timetable-grid th {
            background-color: #1e3a5f;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timetable-grid th:first-child {
            text-align: left;
            padding-left: 10px;
        }

        .timetable-grid td {
            padding: 6px 4px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: middle;
            height: 45px;
        }

        .timetable-grid tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .period-cell {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: left;
            padding-left: 8px !important;
        }

        .period-name {
            color: #1e3a5f;
            font-size: 9px;
        }

        .period-short {
            color: #9ca3af;
            font-size: 7px;
        }

        .time-cell {
            font-size: 8px;
            color: #666;
            white-space: nowrap;
            background-color: #f9fafb;
        }

        .entry-cell {
            min-width: 90px;
        }

        .subject-name {
            font-weight: bold;
            font-size: 9px;
            color: #1e3a5f;
            margin-bottom: 2px;
        }

        .teacher-name {
            font-size: 8px;
            color: #666;
        }

        .room-info {
            font-size: 7px;
            color: #9ca3af;
            margin-top: 1px;
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
            font-size: 9px;
            font-weight: 500;
        }

        .empty-cell {
            color: #d1d5db;
            font-size: 8px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .footer-note {
            margin-top: 5px;
            font-style: italic;
        }

        .legend {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .legend-title {
            font-weight: bold;
            font-size: 9px;
            color: #1e3a5f;
            margin-bottom: 5px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 15px;
            font-size: 8px;
        }

        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            vertical-align: middle;
            margin-right: 4px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
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
                @if($schoolPhone)
                    <div class="school-address">Tel: {{ $schoolPhone }}</div>
                @endif
                <div class="document-title">CLASS TIMETABLE</div>
                <div class="document-subtitle">Academic Year: {{ $academicYear->name }}</div>
            </div>
            @if($schoolLogo && file_exists($schoolLogo))
                <div class="logo-section"></div>
            @endif
        </div>
    </div>

    {{-- Class Info --}}
    <div class="class-info">
        <div class="class-name">{{ $classSection->grade->name ?? 'Unknown Grade' }} - {{ $classSection->name }}</div>
        @if($classSection->classTeacher)
            <div class="class-teacher">Class Teacher: {{ $classSection->classTeacher->name }}</div>
        @endif
    </div>

    {{-- Timetable Grid --}}
    <table class="timetable-grid">
        <thead>
            <tr>
                <th style="width: 12%;">Period</th>
                <th style="width: 8%;">Time</th>
                @foreach($days as $day)
                    <th style="width: 16%;">{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($periods as $period)
                <tr class="{{ $period->isBreak() ? 'break-row' : '' }}">
                    {{-- Period Name --}}
                    <td class="period-cell">
                        <div class="period-name">{{ $period->name }}</div>
                        @if($period->short_name)
                            <div class="period-short">({{ $period->short_name }})</div>
                        @endif
                    </td>

                    {{-- Time --}}
                    <td class="time-cell">
                        {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}<br>
                        {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                    </td>

                    {{-- Day Cells --}}
                    @foreach($days as $day)
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
                                            Lunch Break
                                            @break
                                        @default
                                            Break
                                    @endswitch
                                </span>
                            @else
                                @php
                                    $entry = $timetable[$period->id]['days'][$day] ?? null;
                                @endphp
                                @if($entry)
                                    <div class="subject-name">{{ $entry->subject?->name ?? '-' }}</div>
                                    <div class="teacher-name">{{ $entry->teacher?->name ?? '-' }}</div>
                                    @if($entry->room)
                                        <div class="room-info">{{ $entry->room }}</div>
                                    @endif
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

    {{-- Legend --}}
    <div class="legend">
        <div class="legend-title">Legend:</div>
        <span class="legend-item">
            <span class="legend-color" style="background-color: #fef3c7; border: 1px solid #fcd34d;"></span>
            Break Period
        </span>
        <span class="legend-item">
            <span class="legend-color" style="background-color: #f9fafb; border: 1px solid #e5e7eb;"></span>
            Lesson Period
        </span>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div>{{ $schoolName }} | Generated on {{ $generatedAt }}</div>
        <div class="footer-note">This is a computer-generated document.</div>
    </div>
</body>
</html>
