<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Structure</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 90%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4a7fb5;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 10px;
        }
        .logo-container {
            text-align: center;
        }
        .title {
            font-size: 24px;
            color: #003366;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 18px;
            color: #4a7fb5;
            margin-bottom: 15px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        .fee-section {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4a7fb5;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            background-color: #e6f2ff !important;
        }
        .note {
            font-style: italic;
            margin-top: 30px;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        .contacts {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($schoolLogo))
            <div class="logo-container">
                <img src="{{ $schoolLogo }}" alt="School Logo" class="logo">
            </div>
            @endif
            <h1 class="title">{{ $schoolName ?? 'St. Francis Of Assisi Private School' }}</h1>
            <p class="subtitle">Fee Structure</p>
            @if(isset($schoolAddress))
            <p>{{ $schoolAddress }}</p>
            @endif
            @if(isset($schoolContact))
            <p class="contacts">{{ $schoolContact }}</p>
            @endif
        </div>

        <div class="info-section">
            <div class="info-item">
                <span class="label">Grade:</span>
                <span>{{ $feeStructure->grade }}</span>
            </div>
            <div class="info-item">
                <span class="label">Term:</span>
                <span>{{ $feeStructure->term }}</span>
            </div>
            <div class="info-item">
                <span class="label">Academic Year:</span>
                <span>{{ $feeStructure->academic_year }}</span>
            </div>
            <div class="info-item">
                <span class="label">Date Generated:</span>
                <span>{{ date('F j, Y') }}</span>
            </div>
        </div>

        <div class="fee-section">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Amount (ZMW)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Basic Tuition Fee</td>
                        <td>{{ number_format($feeStructure->basic_fee, 2) }}</td>
                    </tr>

                    @if(is_array($feeStructure->additional_charges))
                        @foreach($feeStructure->additional_charges as $charge)
                            <tr>
                                <td>{{ $charge['description'] }}</td>
                                <td>{{ number_format($charge['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endif

                    <tr class="total">
                        <td><strong>Total Fee</strong></td>
                        <td><strong>{{ number_format($feeStructure->total_fee, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            @if($feeStructure->description)
            <div class="info-item">
                <p><strong>Additional Information:</strong><br>
                {{ $feeStructure->description }}</p>
            </div>
            @endif

            <div class="note">
                <p>Please note:</p>
                <ul>
                    <li>All fees must be paid by the first day of the term</li>
                    <li>Late payments may incur a 5% penalty fee</li>
                    <li>Payment can be made via bank transfer, mobile money or at the school accounts office</li>
                    <li>For any fee-related inquiries, please contact the accounts department</li>
                </ul>
            </div>

            <div class="signature-section">
                <div class="signature">
                    <div class="signature-line">Principal's Signature</div>
                </div>
                <div class="signature">
                    <div class="signature-line">Accounts Officer's Signature</div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is an official document of St. Francis Of Assisi Private School. Any alterations render it invalid.</p>
            <p>© {{ date('Y') }} St. Francis Of Assisi Private School. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
