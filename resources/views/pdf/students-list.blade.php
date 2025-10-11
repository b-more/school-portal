<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Students List</title>
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
            padding: 10px;
            margin-bottom: 15px;
        }
        .summary-box h3 {
            color: #1e40af;
            font-size: 11px;
            margin-bottom: 8px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 5px;
            text-align: center;
        }
        .summary-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
            margin-top: 3px;
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
        <h2>Students List Report</h2>
        <p>Report Type: {{ $reportType }} | Generated on {{ $reportDate }}</p>
    </div>

    {{-- Summary Statistics --}}
    <div class="summary-box">
        <h3>Summary Statistics</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Students</div>
                <div class="summary-value">{{ $students->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Active</div>
                <div class="summary-value" style="color: #059669;">{{ $students->where('enrollment_status', 'active')->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Male</div>
                <div class="summary-value">{{ $students->where('gender', 'Male')->count() }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Female</div>
                <div class="summary-value">{{ $students->where('gender', 'Female')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;">Student ID</th>
                <th style="width: 18%;">Name</th>
                <th style="width: 10%;">Grade</th>
                <th style="width: 8%;">Class</th>
                <th style="width: 6%;">Gender</th>
                <th style="width: 10%;">DOB</th>
                <th style="width: 15%;">Parent/Guardian</th>
                <th style="width: 12%;">Contact</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 8%;">Admitted</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td>{{ $student->student_id_number }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->grade->name ?? 'N/A' }}</td>
                <td>{{ $student->classSection->name ?? 'N/A' }}</td>
                <td>{{ substr($student->gender, 0, 1) }}</td>
                <td>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}</td>
                <td>{{ $student->parentGuardian->name ?? 'N/A' }}</td>
                <td>{{ $student->parentGuardian->phone ?? 'N/A' }}</td>
                <td>
                    @if($student->enrollment_status === 'active')
                        <span class="badge badge-success">Active</span>
                    @elseif($student->enrollment_status === 'inactive')
                        <span class="badge badge-warning">Inactive</span>
                    @elseif($student->enrollment_status === 'graduated')
                        <span class="badge badge-info">Graduated</span>
                    @else
                        <span class="badge badge-danger">{{ ucfirst($student->enrollment_status) }}</span>
                    @endif
                </td>
                <td>{{ $student->admission_date ? $student->admission_date->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @if(($index + 1) % 30 === 0 && $index + 1 < $students->count())
                </tbody>
            </table>
            <div class="page-break"></div>

            {{-- Repeat header on new page --}}
            <div class="header">
                <h1>{{ $schoolName }}</h1>
                <h2>Students List Report (Continued)</h2>
                <p>Page {{ ceil(($index + 1) / 30) + 1 }}</p>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Student ID</th>
                        <th style="width: 18%;">Name</th>
                        <th style="width: 10%;">Grade</th>
                        <th style="width: 8%;">Class</th>
                        <th style="width: 6%;">Gender</th>
                        <th style="width: 10%;">DOB</th>
                        <th style="width: 15%;">Parent/Guardian</th>
                        <th style="width: 12%;">Contact</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 8%;">Admitted</th>
                    </tr>
                </thead>
                <tbody>
            @endif
            @endforeach
        </tbody>
    </table>

    {{-- Grade Distribution --}}
    @if($students->count() > 0)
    <div style="margin-top: 20px; padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
        <h3 style="font-size: 10px; color: #1e40af; margin-bottom: 8px;">Distribution by Grade</h3>
        <table style="width: 100%; font-size: 8px;">
            <tr>
                @php
                    $gradeDistribution = $students->groupBy(fn($s) => $s->grade->name ?? 'Unassigned');
                @endphp
                @foreach($gradeDistribution as $gradeName => $gradeStudents)
                    <td style="padding: 5px; text-align: center;">
                        <strong>{{ $gradeName }}</strong><br>
                        {{ $gradeStudents->count() }} students
                    </td>
                    @if($loop->iteration % 5 === 0 && !$loop->last)
                        </tr><tr>
                    @endif
                @endforeach
            </tr>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $schoolName }} | Generated on {{ $reportDate }}</p>
        <p>Total Records: {{ $students->count() }} | This is a computer-generated document.</p>
    </div>
</body>
</html>
