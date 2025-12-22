<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title') - {{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        /* Header Section */
        .header {
            text-align: center;
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 1px solid #000;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 5px;
        }

        .logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .school-name {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .school-motto {
            font-size: 8pt;
            font-style: italic;
            margin-bottom: 3px;
        }

        .school-contact {
            font-size: 7pt;
            color: #333;
        }

        .report-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #ccc;
        }

        .report-period {
            font-size: 8pt;
            margin-top: 3px;
        }

        /* Summary Section */
        .summary-section {
            margin-bottom: 15px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 33.33%;
            padding: 8px 10px;
            text-align: center;
            border: 1px solid #000;
            vertical-align: top;
        }

        .summary-label {
            font-size: 7pt;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 10pt;
            font-weight: bold;
        }

        /* Section Titles */
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin: 15px 0 8px 0;
            padding: 5px 8px;
            background: #f0f0f0;
            border: 1px solid #000;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }

        table.data-table th {
            background: #f0f0f0;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #000;
        }

        table.data-table th.text-right {
            text-align: right;
        }

        table.data-table th.text-center {
            text-align: center;
        }

        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        table.data-table .total-row {
            background: #f0f0f0;
            font-weight: bold;
        }

        table.data-table .subtotal-row {
            background: #f5f5f5;
            font-weight: bold;
        }

        /* Text Utilities */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        /* Progress Bar */
        .progress-bar-container {
            background: #e0e0e0;
            height: 10px;
            border: 1px solid #999;
        }

        .progress-bar {
            height: 100%;
            background: #666;
        }

        /* Notes Section */
        .notes-section {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #000;
            font-size: 8pt;
        }

        .notes-title {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .notes-list {
            margin: 0;
            padding-left: 15px;
            font-size: 7pt;
        }

        .notes-list li {
            margin-bottom: 2px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 8px 20mm 8px 25mm;
            font-size: 7pt;
            border-top: 1px solid #000;
            background: #fff;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
            width: 60%;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
            width: 40%;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            padding-top: 15px;
        }

        .signature-table {
            width: 100%;
        }

        .signature-table td {
            width: 50%;
            padding: 15px;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 30px;
            font-size: 8pt;
        }

        .confidential-notice {
            text-align: center;
            font-size: 7pt;
            margin-top: 10px;
            font-style: italic;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings && $settings->school_logo)
        <div class="logo-container">
            <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="logo" alt="Logo">
        </div>
        @endif
        <div class="school-name">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
        <div class="school-motto">"{{ $settings->school_motto ?? 'For God and Country' }}"</div>
        <div class="school-contact">
            @if($settings)
                @if($settings->address){{ $settings->address }}@endif
                @if($settings->phone) | Tel: {{ $settings->phone }}@endif
                @if($settings->email) | Email: {{ $settings->email }}@endif
            @endif
        </div>
        <div class="report-title">@yield('title')</div>
        <div class="report-period">@yield('period')</div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                {{ $settings->school_name ?? 'St. Francis of Assisi Private School' }} - Financial Report
            </div>
            <div class="footer-right">
                Generated: {{ $generatedAt->format('d M Y, H:i') }}
            </div>
        </div>
    </div>
</body>
</html>
