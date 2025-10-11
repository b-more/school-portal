<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #4F46E5;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .statistics {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
            min-width: 120px;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #4F46E5;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-present {
            color: #10B981;
            font-weight: bold;
        }
        .status-absent {
            color: #EF4444;
            font-weight: bold;
        }
        .status-late {
            color: #F59E0B;
            font-weight: bold;
        }
        .status-excused {
            color: #3B82F6;
            font-weight: bold;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #4F46E5;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .download-csv {
            position: fixed;
            top: 20px;
            right: 140px;
            padding: 10px 20px;
            background: #10B981;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        @media print {
            .print-button, .download-csv {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Report</button>
    <a href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="download-csv">Download CSV</a>

    <div class="header">
        <h1>Attendance Report</h1>
        <p><strong>St. Francis of Assisi School</strong></p>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        @if($classSection)
            <p>Class: {{ $classSection->grade->name }} - {{ $classSection->name }}</p>
        @endif
    </div>

    <div class="statistics">
        <div class="stat-card">
            <h3>{{ $statistics['total'] }}</h3>
            <p>Total Records</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #10B981;">{{ $statistics['present'] }}</h3>
            <p>Present ({{ $statistics['present_percentage'] }}%)</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #EF4444;">{{ $statistics['absent'] }}</h3>
            <p>Absent ({{ $statistics['absent_percentage'] }}%)</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #F59E0B;">{{ $statistics['late'] }}</h3>
            <p>Late</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #3B82F6;">{{ $statistics['excused'] }}</h3>
            <p>Excused</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Student</th>
                <th>Class</th>
                <th>Status</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->attendance_date->format('d M Y') }}</td>
                    <td>{{ $record->student->name }}</td>
                    <td>{{ $record->grade->name }} - {{ $record->classSection->name }}</td>
                    <td class="status-{{ $record->status }}">{{ ucfirst($record->status) }}</td>
                    <td>{{ $record->check_in_time ? $record->check_in_time->format('H:i') : '-' }}</td>
                    <td>{{ $record->check_out_time ? $record->check_out_time->format('H:i') : '-' }}</td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px;">No attendance records found for the selected period</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 12px;">
        <p>Generated on {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
