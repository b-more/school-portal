<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $schoolName }} - Teacher Schedule</title>
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
            border-bottom: 3px solid #166534;
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
            color: #166534;
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
            color: #166534;
            font-size: 14px;
            font-weight: bold;
            margin-top: 8px;
        }

        .document-subtitle {
            color: #666;
            font-size: 10px;
            margin-top: 3px;
        }

        .teacher-info {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 15px;
        }

        .teacher-name {
            color: #166534;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .teacher-id {
            color: #666;
            font-size: 9px;
            text-align: center;
            margin-top: 3px;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .summary-item {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
            color: #166534;
        }

        .timetable-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .timetable-grid th {
            background-color: #166534;
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
            color: #166534;
            font-size: 9px;
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

        .class-name {
            font-weight: bold;
            font-size: 9px;
            color: #166534;
            margin-bottom: 2px;
        }

        .subject-name {
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

        .free-period {
            color: #10b981;
            font-size: 8px;
            font-style: italic;
        }

        .teaching-summary {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .summary-title {
            font-weight: bold;
            font-size: 10px;
            color: #166534;
            margin-bottom: 8px;
        }

        .summary-row {
            font-size: 8px;
            margin-bottom: 4px;
        }

        .summary-label-inline {
            font-weight: bold;
            color: #333;
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
                <div class="document-title">TEACHER'S WEEKLY SCHEDULE</div>
                <div class="document-subtitle">Academic Year: {{ $academicYear->name }}</div>
            </div>
            @if($schoolLogo && file_exists($schoolLogo))
                <div class="logo-section"></div>
            @endif
        </div>
    </div>

    {{-- Teacher Info --}}
    <div class="teacher-info">
        <div class="teacher-name">{{ $teacher->name }}</div>
        @if($teacher->employee_id)
            <div class="teacher-id">Employee ID: {{ $teacher->employee_id }}</div>
        @endif

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Periods/Week</div>
                <div class="summary-value">{{ $totalPeriods }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Classes</div>
                <div class="summary-value">{{ count($classesTaught) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Subjects</div>
                <div class="summary-value">{{ count($subjectsTaught) }}</div>
            </div>
        </div>
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
                                    <div class="class-name">
                                        {{ $entry->classSection?->grade?->name ?? '' }} {{ $entry->classSection?->name ?? '' }}
                                    </div>
                                    <div class="subject-name">{{ $entry->subject?->name ?? '-' }}</div>
                                    @if($entry->room)
                                        <div class="room-info">{{ $entry->room }}</div>
                                    @endif
                                @else
                                    <span class="free-period">Free</span>
                                @endif
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Teaching Summary --}}
    @if(count($classesTaught) > 0 || count($subjectsTaught) > 0)
        <div class="teaching-summary">
            <div class="summary-title">Teaching Summary</div>
            @if(count($classesTaught) > 0)
                <div class="summary-row">
                    <span class="summary-label-inline">Classes:</span>
                    {{ implode(', ', $classesTaught) }}
                </div>
            @endif
            @if(count($subjectsTaught) > 0)
                <div class="summary-row">
                    <span class="summary-label-inline">Subjects:</span>
                    {{ implode(', ', $subjectsTaught) }}
                </div>
            @endif
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div>{{ $schoolName }} | Generated on {{ $generatedAt }}</div>
        <div class="footer-note">This is a computer-generated document.</div>
    </div>
</body>
</html>
