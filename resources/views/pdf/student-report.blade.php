<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $schoolName }} - Student Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #2563eb;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header h2 {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .header p {
            color: #6b7280;
            font-size: 10px;
        }
        .student-photo {
            float: right;
            width: 100px;
            height: 120px;
            border: 2px solid #e5e7eb;
            margin: 0 0 10px 10px;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #eff6ff;
            color: #1e40af;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: bold;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }
        .info-table td:first-child {
            background-color: #f9fafb;
            font-weight: bold;
            width: 35%;
            color: #374151;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #1e40af;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
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
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .stat-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <h2>Student Report</h2>
        <p>Generated on {{ $reportDate }}</p>
    </div>

    {{-- Student Photo --}}
    @if($student->profile_photo)
        <img src="{{ public_path('storage/' . $student->profile_photo) }}" alt="Student Photo" class="student-photo">
    @endif

    {{-- Personal Information --}}
    <div class="section clearfix">
        <div class="section-title">Personal Information</div>
        <table class="info-table">
            <tr>
                <td>Student ID</td>
                <td><strong>{{ $student->student_id_number }}</strong></td>
            </tr>
            <tr>
                <td>Full Name</td>
                <td>{{ $student->name }}</td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>{{ $student->date_of_birth ? $student->date_of_birth->format('F d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>{{ $student->gender }}</td>
            </tr>
            <tr>
                <td>Place of Birth</td>
                <td>{{ $student->place_of_birth ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Religious Denomination</td>
                <td>{{ $student->religious_denomination ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>{{ $student->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Academic Information --}}
    <div class="section">
        <div class="section-title">Academic Information</div>
        <table class="info-table">
            <tr>
                <td>Grade</td>
                <td>{{ $student->grade->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class Section</td>
                <td>{{ $student->classSection->name ?? 'Not Assigned' }}</td>
            </tr>
            <tr>
                <td>Admission Date</td>
                <td>{{ $student->admission_date ? $student->admission_date->format('F d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Enrollment Status</td>
                <td>
                    @if($student->enrollment_status === 'active')
                        <span class="badge badge-success">Active</span>
                    @elseif($student->enrollment_status === 'inactive')
                        <span class="badge badge-warning">Inactive</span>
                    @else
                        <span class="badge badge-info">{{ ucfirst($student->enrollment_status) }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td>Previous School</td>
                <td>{{ $student->previous_school ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Parent/Guardian Information --}}
    <div class="section">
        <div class="section-title">Parent/Guardian Information</div>
        <table class="info-table">
            <tr>
                <td>Name</td>
                <td>{{ $student->parentGuardian->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Relationship</td>
                <td>{{ $student->parentGuardian->relationship ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Phone</td>
                <td>{{ $student->parentGuardian->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $student->parentGuardian->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Address</td>
                <td>{{ $student->parentGuardian->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    {{-- Statistics Summary --}}
    <div class="section">
        <div class="section-title">Academic Summary</div>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Fee Records</div>
                <div class="stat-value">{{ $student->fees->count() }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Fees</div>
                <div class="stat-value">ZMW {{ number_format($student->fees->sum(fn($fee) => $fee->feeStructure->total_fee ?? 0), 2) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Paid</div>
                <div class="stat-value">ZMW {{ number_format($student->fees->sum('amount_paid'), 2) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Balance</div>
                <div class="stat-value">ZMW {{ number_format($student->fees->sum('balance'), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Recent Fees --}}
    @if($student->fees->count() > 0)
    <div class="section">
        <div class="section-title">Fee Records (Last 5)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Term</th>
                    <th>Total Fee</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->fees->take(5) as $fee)
                <tr>
                    <td>{{ $fee->term->name ?? 'N/A' }}</td>
                    <td>ZMW {{ number_format($fee->feeStructure->total_fee ?? 0, 2) }}</td>
                    <td>ZMW {{ number_format($fee->amount_paid, 2) }}</td>
                    <td>ZMW {{ number_format($fee->balance, 2) }}</td>
                    <td>
                        @if($fee->payment_status === 'paid')
                            <span class="badge badge-success">Paid</span>
                        @elseif($fee->payment_status === 'partial')
                            <span class="badge badge-warning">Partial</span>
                        @else
                            <span class="badge badge-danger">Unpaid</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Attendance Summary --}}
    @if($student->attendances->count() > 0)
    <div class="section">
        <div class="section-title">Attendance Summary (Last 30 Days)</div>
        @php
            $recentAttendance = $student->attendances->sortByDesc('attendance_date')->take(30);
            $presentCount = $recentAttendance->where('status', 'present')->count();
            $absentCount = $recentAttendance->where('status', 'absent')->count();
            $lateCount = $recentAttendance->where('status', 'late')->count();
            $totalDays = $recentAttendance->count();
            $attendanceRate = $totalDays > 0 ? round(($presentCount / $totalDays) * 100, 1) : 0;
        @endphp
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-label">Total Days</div>
                <div class="stat-value">{{ $totalDays }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Present</div>
                <div class="stat-value" style="color: #059669;">{{ $presentCount }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Absent</div>
                <div class="stat-value" style="color: #dc2626;">{{ $absentCount }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-value">{{ $attendanceRate }}%</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Medical Information --}}
    @if($student->medical_information)
    <div class="section">
        <div class="section-title">Medical Information</div>
        <table class="info-table">
            <tr>
                <td>Medical Notes</td>
                <td>{{ $student->medical_information }}</td>
            </tr>
            <tr>
                <td>Smallpox Vaccination</td>
                <td>{{ $student->smallpox_vaccination ?? 'N/A' }}</td>
            </tr>
            @if($student->date_vaccinated)
            <tr>
                <td>Date Vaccinated</td>
                <td>{{ $student->date_vaccinated }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    {{-- Additional Notes --}}
    @if($student->notes)
    <div class="section">
        <div class="section-title">Additional Notes</div>
        <p style="padding: 10px; background-color: #f9fafb; border: 1px solid #e5e7eb;">
            {{ $student->notes }}
        </p>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>{{ $schoolName }} | Generated on {{ $reportDate }}</p>
        <p>This is a computer-generated document and does not require a signature.</p>
    </div>
</body>
</html>
