<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Homework Assignment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .homework-details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
            border-radius: 3px;
        }
        .detail-row {
            margin: 10px 0;
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }
        .value {
            color: #333;
        }
        .description {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button:hover {
            background-color: #45a049;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 New Homework Assignment</h1>
    </div>

    <div class="content">
        <p>Dear {{ $parent_name }},</p>

        <p>Your child <strong>{{ $student_name }}</strong> has received a new homework assignment.</p>

        <div class="homework-details">
            <h3 style="margin-top: 0; color: #4CAF50;">Homework Details</h3>

            <div class="detail-row">
                <span class="label">Subject:</span>
                <span class="value">{{ $subject_name }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Title:</span>
                <span class="value">{{ $homework_title }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Grade:</span>
                <span class="value">{{ $grade_name }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Teacher:</span>
                <span class="value">{{ $teacher_name }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Due Date:</span>
                <span class="value"><strong>{{ $due_date }}</strong></span>
            </div>

            @if($max_score)
            <div class="detail-row">
                <span class="label">Maximum Score:</span>
                <span class="value">{{ $max_score }} points</span>
            </div>
            @endif
        </div>

        @if($homework_description)
        <div class="homework-details">
            <h4 style="margin-top: 0; color: #555;">Instructions:</h4>
            <div class="description">{{ $homework_description }}</div>
        </div>
        @endif

        <div class="alert">
            <strong>⏰ Important:</strong> Please ensure your child completes this assignment before the due date.
        </div>

        <div style="text-align: center;">
            <a href="{{ config('app.url') }}" class="button">
                View in Parent Portal
            </a>
        </div>

        <p style="margin-top: 20px;">
            You can access the full homework details, download any attached files, and track your child's progress through the parent portal.
        </p>

        <p>
            If you have any questions or concerns, please don't hesitate to contact the teacher or school office.
        </p>

        <p>Best regards,<br>
        <strong>St. Francis of Assisi School</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated notification from St. Francis of Assisi School Management System.</p>
        <p>Please do not reply to this email. For inquiries, please contact the school office or use the parent portal.</p>
    </div>
</body>
</html>
