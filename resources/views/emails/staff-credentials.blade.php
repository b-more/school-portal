<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Account Created</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .credentials-box {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #1e40af;
            font-size: 18px;
        }
        .credential-item {
            margin: 15px 0;
            padding: 12px;
            background-color: #ffffff;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }
        .credential-label {
            font-weight: bold;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .credential-value {
            font-size: 16px;
            color: #1e293b;
            font-family: 'Courier New', monospace;
            margin-top: 5px;
            word-break: break-all;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .warning-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box p {
            margin: 0;
            color: #92400e;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        ul {
            padding-left: 20px;
        }
        ul li {
            margin: 8px 0;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to St Francis of Assisi School Portal</h1>
            <p>Your Account Has Been Created</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $name }}</strong>,</p>

            <p>Your portal account has been successfully created. You can now access the school management system using the credentials below.</p>

            <div class="credentials-box">
                <h3>🔐 Your Login Credentials</h3>

                <div class="credential-item">
                    <div class="credential-label">Email / Username</div>
                    <div class="credential-value">{{ $email }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label">Temporary Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>

                <div class="credential-item">
                    <div class="credential-label">Role</div>
                    <div class="credential-value">{{ $role }}</div>
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ $portalUrl }}" class="button">Access Portal →</a>
            </div>

            <div class="warning-box">
                <p><strong>⚠️ Important Security Notice:</strong> Please change your password immediately after your first login to ensure account security.</p>
            </div>

            <h4 style="color: #1e40af; margin-top: 25px;">First Login Steps:</h4>
            <ul>
                <li>Visit the portal at: <a href="{{ $portalUrl }}">{{ $portalUrl }}</a></li>
                <li>Enter your email as username: <strong>{{ $email }}</strong></li>
                <li>Enter your temporary password provided above</li>
                <li>You'll be prompted to change your password on first login</li>
                <li>Create a strong password (min. 8 characters with letters, numbers, and symbols)</li>
            </ul>

            <h4 style="color: #1e40af; margin-top: 25px;">Need Help?</h4>
            <p>If you encounter any issues logging in or have questions about using the portal, please contact:</p>
            <ul>
                <li>IT Support: <a href="mailto:support@stfrancisofassisi.tech">support@stfrancisofassisi.tech</a></li>
                <li>School Office: Phone contact available during school hours</li>
            </ul>
        </div>

        <div class="footer">
            <p><strong>St Francis of Assisi School</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p style="margin-top: 15px; color: #94a3b8;">
                For security reasons, keep your login credentials confidential and never share them with anyone.
            </p>
        </div>
    </div>
</body>
</html>
