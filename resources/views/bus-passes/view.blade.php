<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Pass - {{ $busPayment->student->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .bus-pass-container {
            max-width: 450px;
            width: 100%;
        }

        .bus-pass {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
        }

        .pass-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 20px;
            text-align: center;
            position: relative;
        }

        .pass-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .school-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            color: #667eea;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .school-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .pass-type {
            font-size: 14px;
            opacity: 0.95;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .pass-body {
            padding: 30px 20px 20px;
        }

        .student-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            font-weight: bold;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .student-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .student-name {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            color: #222;
            margin-bottom: 5px;
        }

        .student-id {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .pass-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 13px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 15px;
            color: #222;
            font-weight: 600;
        }

        .route-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-section {
            text-align: center;
            padding: 25px 20px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            margin: 0 -20px -20px;
        }

        .status-badge {
            display: inline-block;
            background: white;
            color: #11998e;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            margin-bottom: 15px;
        }

        .verification-code {
            font-size: 14px;
            opacity: 0.95;
            margin-top: 10px;
        }

        .qr-code {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 12px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        .info-text {
            text-align: center;
            font-size: 12px;
            color: rgba(255,255,255,0.9);
            margin-top: 15px;
            line-height: 1.6;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .bus-pass-container {
                max-width: 100%;
            }

            .actions {
                display: none;
            }

            @page {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="bus-pass-container">
        <div class="bus-pass">
            <!-- Header -->
            <div class="pass-header">
                <div class="school-logo">SFA</div>
                <div class="school-name">St. Francis of Assisi School</div>
                <div class="pass-type">School Bus Pass</div>
            </div>

            <!-- Body -->
            <div class="pass-body">
                <!-- Student Photo -->
                <div class="student-photo">
                    @if($busPayment->student->profile_photo)
                        <img src="{{ Storage::url($busPayment->student->profile_photo) }}" alt="{{ $busPayment->student->name }}">
                    @else
                        {{ strtoupper(substr($busPayment->student->name, 0, 1)) }}
                    @endif
                </div>

                <!-- Student Info -->
                <div class="student-name">{{ $busPayment->student->name }}</div>
                <div class="student-id">ID: {{ $busPayment->student->student_id_number ?? 'N/A' }}</div>

                <!-- Pass Details -->
                <div class="pass-details">
                    <div class="detail-row">
                        <span class="detail-label">Route</span>
                        <span class="route-badge">{{ $busPayment->busFareStructure->route_name }}</span>
                    </div>

                    @if($busPayment->month)
                    <div class="detail-row">
                        <span class="detail-label">Valid For</span>
                        <span class="detail-value">{{ $busPayment->month }} {{ $busPayment->year }}</span>
                    </div>
                    @else
                    <div class="detail-row">
                        <span class="detail-label">Valid For</span>
                        <span class="detail-value">Full Term {{ $busPayment->year }}</span>
                    </div>
                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Grade</span>
                        <span class="detail-value">{{ $busPayment->student->grade->name ?? 'N/A' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Amount Paid</span>
                        <span class="detail-value">ZMW {{ number_format($busPayment->amount_paid, 2) }}</span>
                    </div>

                    @if($busPayment->balance > 0)
                    <div class="detail-row">
                        <span class="detail-label">Balance</span>
                        <span class="detail-value" style="color: #e74c3c;">ZMW {{ number_format($busPayment->balance, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Status Section -->
            <div class="status-section">
                <div class="status-badge">
                    @if($busPayment->payment_status === 'paid')
                        ✓ VALID
                    @elseif($busPayment->payment_status === 'partial')
                        PARTIAL PAYMENT
                    @endif
                </div>

                <div class="qr-code">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=BUS-PASS-{{ $busPayment->id }}-{{ $busPayment->student->student_id_number }}" alt="QR Code">
                </div>

                <div class="verification-code">
                    Verification Code: BUS-{{ str_pad($busPayment->id, 6, '0', STR_PAD_LEFT) }}
                </div>

                <div class="info-text">
                    Present this pass when boarding the bus.<br>
                    Valid for {{ $busPayment->busFareStructure->route_name }} route only.
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions">
            <button onclick="window.print()" class="btn">Print Pass</button>
            <a href="{{ url()->previous() }}" class="btn">Back</a>
        </div>
    </div>
</body>
</html>
