<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Approval Letter - {{ $leaveApplication->reference_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 30px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px double #1a365d;
            padding-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        .school-motto {
            font-style: italic;
            color: #666;
            margin: 3px 0;
            font-size: 11px;
        }

        .school-address {
            font-size: 10px;
            color: #555;
            margin-top: 5px;
            line-height: 1.4;
        }

        .document-title {
            text-align: center;
            margin: 20px 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1a365d;
            text-decoration: underline;
        }

        .reference-section {
            margin-bottom: 15px;
            font-size: 11px;
        }

        .reference-left {
            float: left;
            width: 50%;
        }

        .reference-right {
            float: right;
            width: 50%;
            text-align: right;
        }

        .clearfix {
            clear: both;
        }

        .status-line {
            text-align: right;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .content {
            margin: 25px 0;
            text-align: justify;
        }

        .content p {
            margin-bottom: 12px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .details-table th,
        .details-table td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }

        .details-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 35%;
            color: #1a365d;
        }

        .details-table td {
            background-color: #fff;
        }

        .approval-section {
            margin: 20px 0;
            padding: 12px;
            background-color: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 5px;
        }

        .approval-section h4 {
            color: #16a34a;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .approval-details {
            font-size: 10px;
        }

        .approval-details p {
            margin: 4px 0;
        }

        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table;
            width: 100%;
            margin-top: 35px;
        }

        .signature-box {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .signature-box.right {
            text-align: right;
        }

        .signature-line {
            border-top: 1px solid #333;
            width: 180px;
            margin-bottom: 5px;
        }

        .signature-box.right .signature-line {
            margin-left: auto;
        }

        .signature-name {
            font-weight: bold;
            font-size: 11px;
        }

        .signature-title {
            font-size: 10px;
            color: #666;
        }

        .footer {
            margin-top: 40px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #999;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 70px;
            color: rgba(34, 197, 94, 0.08);
            font-weight: bold;
            z-index: -1;
        }

        .note-box {
            margin: 15px 0;
            padding: 10px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            font-size: 11px;
        }

        .note-box strong {
            color: #b45309;
        }
    </style>
</head>
<body>
    @if($leaveApplication->status === 'approved')
    <div class="watermark">APPROVED</div>
    @endif

    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
        <div class="school-name">St. Francis of Assisi Private School</div>
        <div class="school-motto">"For God and For Country"</div>
        <div class="school-address">
            1310/4 East Kamenza, Chililabombwe, Zambia<br>
            Tel: +260 972 266 217 | Email: info@stfrancisschool.tech
        </div>
    </div>

    <div class="document-title">Leave Approval Letter</div>

    <div class="reference-section">
        <div class="reference-left">
            <strong>Reference:</strong> {{ $leaveApplication->reference_number }}<br>
            <strong>Employee ID:</strong> {{ $leaveApplication->employee->employee_number ?? $leaveApplication->employee->employee_id ?? 'N/A' }}<br>
            <strong>Date:</strong> {{ now()->format('d F Y') }}
        </div>
        <div class="reference-right">
            &nbsp;
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="status-line">
        <strong>Status:</strong>
        <span class="status-badge status-{{ $leaveApplication->status }}">
            {{ ucfirst(str_replace('_', ' ', $leaveApplication->status)) }}
        </span>
    </div>

    <div class="content">
        <p>Dear <strong>{{ $leaveApplication->employee->name }}</strong>,</p>

        @if($leaveApplication->status === 'approved')
        <p>
            We are pleased to inform you that your leave application has been <strong>approved</strong>.
            Please find the details of your approved leave below:
        </p>
        @elseif($leaveApplication->status === 'rejected')
        <p>
            We regret to inform you that your leave application has been <strong>rejected</strong>.
            Please find the details below:
        </p>
        @else
        <p>
            This letter confirms the current status of your leave application.
            Please find the details below:
        </p>
        @endif

        <table class="details-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $leaveApplication->employee->name }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $leaveApplication->employee->department ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Position</th>
                <td>{{ $leaveApplication->employee->position ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Leave Type</th>
                <td>{{ $leaveApplication->leaveType->name }}</td>
            </tr>
            <tr>
                <th>Leave Period</th>
                <td>
                    {{ \Carbon\Carbon::parse($leaveApplication->start_date)->format('d F Y') }}
                    to
                    {{ \Carbon\Carbon::parse($leaveApplication->end_date)->format('d F Y') }}
                    @if($leaveApplication->is_half_day)
                        ({{ ucfirst($leaveApplication->half_day_period) }} Half Day)
                    @endif
                </td>
            </tr>
            <tr>
                <th>Number of Working Days</th>
                <td>{{ $leaveApplication->days_requested }} day(s) <small style="color: #666;">(excludes weekends)</small></td>
            </tr>
            <tr>
                <th>Reason</th>
                <td>{{ $leaveApplication->reason }}</td>
            </tr>
            @if($leaveApplication->covering_employee_id)
            <tr>
                <th>Covering Employee</th>
                <td>{{ $leaveApplication->coveringEmployee->name ?? 'N/A' }}</td>
            </tr>
            @endif
            @if($leaveApplication->contact_during_leave)
            <tr>
                <th>Contact During Leave</th>
                <td>{{ $leaveApplication->contact_during_leave }}</td>
            </tr>
            @endif
        </table>

        @if($leaveApplication->status === 'approved')
        <div class="approval-section">
            <h4>Approval Information</h4>
            <div class="approval-details">
                @if($leaveApplication->hod_approved_at)
                <p><strong>HOD Approval:</strong> {{ \Carbon\Carbon::parse($leaveApplication->hod_approved_at)->format('d M Y, H:i') }}
                    @if($leaveApplication->hodApprover) by {{ $leaveApplication->hodApprover->name }} @endif
                </p>
                @endif
                @if($leaveApplication->head_approved_at)
                <p><strong>Headteacher Approval:</strong> {{ \Carbon\Carbon::parse($leaveApplication->head_approved_at)->format('d M Y, H:i') }}
                    @if($leaveApplication->headApprover) by {{ $leaveApplication->headApprover->name }} @endif
                </p>
                @endif
                @if($leaveApplication->approved_at)
                <p><strong>Final Approval:</strong> {{ \Carbon\Carbon::parse($leaveApplication->approved_at)->format('d M Y, H:i') }}
                    @if($leaveApplication->approver) by {{ $leaveApplication->approver->name }} @endif
                </p>
                @endif
                @if($leaveApplication->approval_remarks)
                <p><strong>Remarks:</strong> {{ $leaveApplication->approval_remarks }}</p>
                @endif
            </div>
        </div>

        <p>
            Please ensure that all pending work is handed over to your covering colleague before
            proceeding on leave. You are expected to resume duty on
            <strong>{{ \Carbon\Carbon::parse($leaveApplication->end_date)->addDay()->format('d F Y') }}</strong>.
        </p>
        @endif

        @if($leaveApplication->status === 'rejected')
        <div class="note-box">
            <strong>Reason for Rejection:</strong><br>
            {{ $leaveApplication->rejection_reason ?? 'No specific reason provided.' }}
        </div>
        <p>
            If you have any questions regarding this decision, please contact the Human Resources
            department or your immediate supervisor.
        </p>
        @endif

        @if($leaveApplication->handover_notes)
        <div class="note-box">
            <strong>Handover Notes:</strong><br>
            {{ $leaveApplication->handover_notes }}
        </div>
        @endif

        <p>
            We wish you a restful leave period.
        </p>

        <p>Yours sincerely,</p>
    </div>

    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-box">
                <div class="signature-line"></div>
                <div class="signature-name">Human Resource/ Headteacher</div>
                <div class="signature-title">St. Francis of Assisi Private School</div>
            </div>
            <div class="signature-box right">
                <div class="signature-line"></div>
                <div class="signature-name">Executive Director</div>
                <div class="signature-title">St. Francis of Assisi Private School</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document and is valid without a physical signature.</p>
        <p>Reference: {{ $leaveApplication->reference_number }} | Generated on: {{ now()->format('d M Y, H:i:s') }}</p>
        <p>St. Francis of Assisi Private School | 1310/4 East Kamenza, Chililabombwe, Zambia</p>
    </div>
</body>
</html>
