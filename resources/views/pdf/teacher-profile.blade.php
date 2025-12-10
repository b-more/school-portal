<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - {{ $teacher->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
        }

        .container {
            width: 100%;
            padding: 15px;
        }

        /* Compact Header Section */
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 8px;
        }

        .logo {
            width: 65px;
            height: 65px;
            margin: 0 auto 6px;
            object-fit: contain;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .school-subtitle {
            font-size: 9pt;
            opacity: 0.9;
            margin-bottom: 8px;
        }

        .document-title {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            padding: 6px 0;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        /* Compact Profile Section */
        .profile-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
        }

        .profile-image-container {
            display: table-cell;
            width: 100px;
            vertical-align: top;
            padding-right: 12px;
        }

        .profile-image {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            border: 3px solid #3b82f6;
            object-fit: cover;
        }

        .profile-info {
            display: table-cell;
            vertical-align: top;
        }

        .profile-name {
            font-size: 15pt;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 3px;
        }

        .profile-id {
            font-size: 9pt;
            color: #64748b;
            margin-bottom: 8px;
        }

        .badges {
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            background: #3b82f6;
            color: white;
            border-radius: 12px;
            font-size: 7pt;
            margin-right: 5px;
            margin-bottom: 3px;
        }

        .badge-green {
            background: #10b981;
        }

        /* Compact Info Grid */
        .info-section {
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 2px solid #3b82f6;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 0;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            padding: 5px 10px;
            font-weight: bold;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
        }

        .info-value {
            display: table-cell;
            padding: 5px 10px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8pt;
        }

        /* Compact Biography Section */
        .biography-box {
            background: #f8fafc;
            padding: 8px;
            border-left: 3px solid #3b82f6;
            border-radius: 4px;
            margin-top: 6px;
            font-size: 8pt;
        }

        /* Compact Footer */
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 7pt;
        }

        .footer-info {
            margin-bottom: 3px;
        }

        .generated-date {
            font-style: italic;
            color: #94a3b8;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                @if(file_exists(public_path('images/logo.png')))
                    <img src="{{ public_path('images/logo.png') }}" alt="St Francis Logo" class="logo">
                @else
                    <div style="width: 65px; height: 65px; background: white; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; color: #1e3a8a; font-size: 20pt; font-weight: bold;">SF</div>
                @endif
                <div class="school-name">St Francis of Assisi</div>
                <div class="school-subtitle">Excellence in Education</div>
            </div>
            <div class="document-title">TEACHER PROFILE</div>
        </div>

        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-image-container">
                @if($teacher->profile_photo && Storage::disk('public')->exists($teacher->profile_photo))
                    <img src="{{ Storage::disk('public')->path($teacher->profile_photo) }}" alt="Profile Photo" class="profile-image">
                @else
                    <div style="width: 90px; height: 90px; background: linear-gradient(135deg, #3b82f6 0%, #1e3a8a 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 28pt; font-weight: bold; border: 3px solid #3b82f6;">
                        {{ strtoupper(substr($teacher->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="profile-info">
                <div class="profile-name">{{ $teacher->name }}</div>
                <div class="profile-id">Employee ID: {{ $teacher->employee_id }}</div>

                <div style="margin-top: 6px; font-size: 8pt;">
                    <div style="margin-bottom: 3px;"><strong>Qualification:</strong> {{ $teacher->qualification ?? 'N/A' }}</div>
                    <div style="margin-bottom: 3px;"><strong>Date Joined:</strong> {{ $teacher->join_date ? $teacher->join_date->format('M d, Y') : 'N/A' }}</div>
                    @if($teacher->specialization)
                    <div style="margin-bottom: 3px;"><strong>Specialization:</strong> {{ $teacher->specialization }}</div>
                    @endif
                </div>

                <div class="badges">
                    @if($teacher->is_class_teacher)
                        <span class="badge badge-green">Class Teacher</span>
                    @endif
                    @if($teacher->is_grade_teacher)
                        <span class="badge">Grade Teacher</span>
                    @endif
                    @if($teacher->is_active)
                        <span class="badge badge-green">Active</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="info-section">
            <div class="section-title">Personal Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $teacher->email ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">{{ $teacher->phone ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $teacher->address ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Identification & Banking -->
        <div class="info-section">
            <div class="section-title">Identification & Banking Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">NRC Number</div>
                    <div class="info-value">{{ $teacher->nrc ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">TPIN</div>
                    <div class="info-value">{{ $teacher->tpin ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Account Number</div>
                    <div class="info-value">{{ $teacher->account_number ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bank Name</div>
                    <div class="info-value">{{ $teacher->bank_name ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Bank Branch</div>
                    <div class="info-value">{{ $teacher->bank_branch ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Teaching Assignment -->
        @if($teacher->grade || $teacher->classSection)
        <div class="info-section">
            <div class="section-title">Teaching Assignment</div>
            <div class="info-grid">
                @if($teacher->grade)
                <div class="info-row">
                    <div class="info-label">Assigned Grade</div>
                    <div class="info-value">{{ $teacher->grade->name }}</div>
                </div>
                @endif
                @if($teacher->classSection)
                <div class="info-row">
                    <div class="info-label">Class Section</div>
                    <div class="info-value">{{ $teacher->classSection->grade->name ?? '' }} - {{ $teacher->classSection->name }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Biography -->
        @if($teacher->biography)
        <div class="info-section">
            <div class="section-title">Biography / Notes</div>
            <div class="biography-box">
                {{ $teacher->biography }}
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-info">
                St Francis of Assisi School | Excellence in Education
            </div>
            <div class="footer-info">
                Email: info@stfrancisofassisi.tech | Phone: +260 XXX XXX XXX
            </div>
            <div class="generated-date">
                Generated on: {{ now()->format('F d, Y \a\t h:i A') }}
            </div>
        </div>
    </div>
</body>
</html>
