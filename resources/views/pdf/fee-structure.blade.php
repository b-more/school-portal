<!DOCTYPE html>
<html>
<head>
    <title>Fee Structure</title>
    <style>
        /* Base styling for clean, professional look */
        body {
            font-family: Arial, sans-serif;
            margin: 15px;
            padding: 0;
            color: #333;
            font-size: 11pt;
            line-height: 1.3;
        }

        /* Header section with logo and title */
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .logo {
            max-width: 70px;
            margin-bottom: 5px;
        }
        .school-title {
            font-size: 16pt;
            font-weight: bold;
            color: #003366;
            margin: 5px 0 2px 0;
        }
        .document-title {
            font-size: 14pt;
            color: #4b86c4;
            margin: 2px 0;
        }

        /* School contact info */
        .school-info {
            text-align: center;
            color: #555;
            margin-bottom: 15px;
            font-size: 9pt;
        }
        .school-info p {
            margin: 0;
            padding: 0;
        }

        /* Line separator */
        .divider {
            border-bottom: 1px solid #4b86c4;
            margin: 10px 0;
        }

        /* Student info section */
        .info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 10px;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        .info-item {
            margin: 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
        }

        /* Fee table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }
        th {
            background-color: #4b86c4;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 7px 10px;
            border: 1px solid #ddd;
        }
        td {
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .amount-column {
            text-align: right;
        }
        .total-row {
            background-color: #e6f2ff !important;
            font-weight: bold;
        }

        /* Additional information section */
        .additional-info {
            margin-top: 15px;
            font-size: 10pt;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .additional-info p {
            margin: 0;
        }

        /* Footer sections */
        .footer-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 20px;
            margin-top: 15px;
            font-size: 9pt;
        }

        /* Notes section */
        .notes-section {
            margin-top: 0;
        }
        .notes-section p {
            margin: 0 0 3px 0;
            font-weight: bold;
        }
        .notes-section ul {
            margin: 0;
            padding-left: 15px;
        }
        .notes-section li {
            margin-bottom: 2px;
        }

        /* Signatures section */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 40px;
            margin-top: 20px;
        }
        .signature-block {
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 0 auto 2px auto;
        }
        .signature-img {
            height: 60px; /* INCREASED FROM 40px to 60px */
            margin-bottom: 5px;
            max-width: 150px; /* Added to maintain proportions */
        }

        /* Fine print section */
        .fine-print {
            text-align: center;
            margin-top: 15px;
            font-size: 8pt;
            color: #666;
        }
        .disclaimer {
            font-style: italic;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="logo">
        <div class="school-title">St. Francis Of Assisi Private School</div>
        <div class="document-title">Fee Structure</div>
    </div>

    <div class="school-info">
        <p>Plot No 1310/4 East Kamenza, Chililabombwe, Zambia</p>
        <p>Phone: +260 972 266 217, Email: info@stfrancisofassisi.tech</p>
    </div>

    <div class="divider"></div>

    <div class="info-section">
        <p class="info-item"><span class="info-label">Grade:</span> {{ $grade }}</p>
        <p class="info-item"><span class="info-label">Term:</span> {{ $term }}</p>
        <p class="info-item"><span class="info-label">Academic Year:</span> {{ $academicYear }}</p>
        <p class="info-item"><span class="info-label">Date Generated:</span> {{ date('F j, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 30%; text-align: right;">Amount (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Tuition Fee</td>
                <td class="amount-column">{{ number_format($feeStructure->basic_fee, 2) }}</td>
            </tr>

            @php
                $additionalCharges = $feeStructure->additional_charges;
                if (is_array($additionalCharges)) {
                    foreach ($additionalCharges as $charge) {
                        if (isset($charge['description']) && isset($charge['amount'])) {
                            echo '<tr>';
                            echo '<td>' . $charge['description'] . '</td>';
                            echo '<td class="amount-column">' . number_format($charge['amount'], 2) . '</td>';
                            echo '</tr>';
                        }
                    }
                }
            @endphp

            <tr class="total-row">
                <td>Total Fee</td>
                <td class="amount-column">{{ number_format($feeStructure->total_fee, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="additional-info">
        <div class="section-title">Additional Information:</div>
        <p>{{ $feeStructure->description ?? $grade . ' ' . $term . ' ' . $academicYear . ' Fee Structure' }}</p>
    </div>

    <div class="footer-content">
        <div class="notes-section">
            <p>Please note:</p>
            <ul>
                <li>All fees must be paid by the first day of the term</li>
                <li>Late payments may incur a 5% penalty fee</li>
                <li>Payment can be made via bank transfer, mobile money or at the school accounts office</li>
                <li>For any fee-related inquiries, please contact the accounts department</li>
            </ul>
        </div>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <img src="{{ public_path('images/ed_signature.png') }}" alt="Executive Director Signature" class="signature-img">
            <div class="signature-line"></div>
            <p>Executive Director's Signature</p>
        </div>
        <div class="signature-block">
            <div style="height: 60px;"></div> <!-- Also increased from 40px to 60px -->
            <div class="signature-line"></div>
            <p>Accounts Officer's Signature</p>
        </div>
    </div>

    <div class="fine-print">
        <p class="disclaimer">This is an official document of {{ $schoolName }}. Any alterations render it invalid.</p>
        <p>© {{ date('Y') }} {{ $schoolName }}. All Rights Reserved.</p>
    </div>
</body>
</html>
