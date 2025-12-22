<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>School Portal Quick Guide</title>
    <style>
        @page {
            margin: 20mm 15mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }

        .cover-page {
            text-align: center;
            padding-top: 100px;
            page-break-after: always;
        }

        .cover-title {
            font-size: 32px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 10px;
        }

        .cover-subtitle {
            font-size: 18px;
            color: #dc2626;
            margin-bottom: 40px;
        }

        .cover-school {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a5f;
            margin-top: 60px;
        }

        .cover-motto {
            font-size: 14px;
            font-style: italic;
            color: #666;
            margin-top: 10px;
        }

        .cover-date {
            font-size: 12px;
            color: #888;
            margin-top: 100px;
        }

        h1 {
            font-size: 20px;
            color: #1e3a5f;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 8px;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        h2 {
            font-size: 16px;
            color: #1e3a5f;
            margin-top: 20px;
            margin-bottom: 10px;
            border-left: 4px solid #dc2626;
            padding-left: 10px;
        }

        h3 {
            font-size: 13px;
            color: #2c5282;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .section {
            margin-bottom: 20px;
        }

        .info-box {
            background: #f0f7ff;
            border: 1px solid #1e3a5f;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
        }

        .warning-box {
            background: #fff5f5;
            border: 1px solid #dc2626;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
        }

        .tip-box {
            background: #f0fff4;
            border: 1px solid #059669;
            border-radius: 5px;
            padding: 12px;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th {
            background: #1e3a5f;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 10px;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .step {
            display: flex;
            margin: 8px 0;
        }

        .step-number {
            background: #1e3a5f;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            text-align: center;
            line-height: 22px;
            font-weight: bold;
            font-size: 11px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .step-content {
            flex: 1;
        }

        ul, ol {
            margin: 5px 0 10px 20px;
            padding: 0;
        }

        li {
            margin: 4px 0;
        }

        .toc {
            page-break-after: always;
        }

        .toc-title {
            font-size: 20px;
            color: #1e3a5f;
            margin-bottom: 20px;
            text-align: center;
        }

        .toc-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .toc-page {
            float: right;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }

        .widget-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            margin: 8px 0;
            background: #fafafa;
        }

        .widget-title {
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Cover Page -->
    <div class="cover-page">
        <div class="cover-title">SCHOOL PORTAL</div>
        <div class="cover-subtitle">Quick Reference Guide</div>

        <div style="margin: 40px 0;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="#1e3a5f">
                <path d="M12 3L1 9l11 6 9-4.91V17h2V9M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/>
            </svg>
        </div>

        <div class="cover-school">St. Francis of Assisi Private School</div>
        <div class="cover-motto">"For God and For Country"</div>

        <div class="cover-date">
            Version 1.0 | {{ now()->format('F Y') }}
        </div>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
        <div class="toc-title">Table of Contents</div>

        <div class="toc-item"><strong>1. Getting Started</strong> <span class="toc-page">3</span></div>
        <div class="toc-item" style="padding-left: 20px;">1.1 Logging In</div>
        <div class="toc-item" style="padding-left: 20px;">1.2 Dashboard Overview</div>
        <div class="toc-item" style="padding-left: 20px;">1.3 Navigation</div>

        <div class="toc-item"><strong>2. Dashboard Widgets Explained</strong> <span class="toc-page">4</span></div>
        <div class="toc-item" style="padding-left: 20px;">2.1 Statistics Cards</div>
        <div class="toc-item" style="padding-left: 20px;">2.2 Charts & Graphs</div>
        <div class="toc-item" style="padding-left: 20px;">2.3 Attention Items</div>

        <div class="toc-item"><strong>3. Student Management</strong> <span class="toc-page">6</span></div>
        <div class="toc-item" style="padding-left: 20px;">3.1 Adding New Students</div>
        <div class="toc-item" style="padding-left: 20px;">3.2 Managing Enrollments</div>
        <div class="toc-item" style="padding-left: 20px;">3.3 Student Records</div>

        <div class="toc-item"><strong>4. Academic Management</strong> <span class="toc-page">7</span></div>
        <div class="toc-item" style="padding-left: 20px;">4.1 Classes & Sections</div>
        <div class="toc-item" style="padding-left: 20px;">4.2 Subjects & Curriculum</div>
        <div class="toc-item" style="padding-left: 20px;">4.3 Entering Results</div>
        <div class="toc-item" style="padding-left: 20px;">4.4 Report Cards</div>

        <div class="toc-item"><strong>5. Attendance Tracking</strong> <span class="toc-page">9</span></div>
        <div class="toc-item" style="padding-left: 20px;">5.1 Marking Attendance</div>
        <div class="toc-item" style="padding-left: 20px;">5.2 Attendance Reports</div>

        <div class="toc-item"><strong>6. Fee Management</strong> <span class="toc-page">10</span></div>
        <div class="toc-item" style="padding-left: 20px;">6.1 Fee Structures</div>
        <div class="toc-item" style="padding-left: 20px;">6.2 Recording Payments</div>
        <div class="toc-item" style="padding-left: 20px;">6.3 Generating Invoices</div>

        <div class="toc-item"><strong>7. Homework & Assignments</strong> <span class="toc-page">11</span></div>

        <div class="toc-item"><strong>8. Reports & Analytics</strong> <span class="toc-page">12</span></div>

        <div class="toc-item"><strong>9. Quick Reference</strong> <span class="toc-page">13</span></div>
    </div>

    <!-- Section 1: Getting Started -->
    <h1>1. Getting Started</h1>

    <h2>1.1 Logging In</h2>
    <div class="section">
        <p>Access the school portal by navigating to your school's portal URL in your web browser.</p>

        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Enter your registered email address in the Email field</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Enter your password in the Password field</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Click the "Sign in" button to access your dashboard</div>
        </div>

        <div class="tip-box">
            <strong>Tip:</strong> If you've forgotten your password, contact the system administrator to reset it.
        </div>
    </div>

    <h2>1.2 Dashboard Overview</h2>
    <div class="section">
        <p>The dashboard provides a comprehensive overview of your school's key metrics and quick access to common tasks.</p>

        <table>
            <tr>
                <th>Component</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><strong>Welcome Header</strong></td>
                <td>Displays greeting, current date/time, and school term information</td>
            </tr>
            <tr>
                <td><strong>Quick Actions</strong></td>
                <td>Shortcuts to frequently used features (Students, Teachers, Fees, etc.)</td>
            </tr>
            <tr>
                <td><strong>Statistics Cards</strong></td>
                <td>Key metrics showing students, teachers, fees, attendance, homework, events</td>
            </tr>
            <tr>
                <td><strong>Attention Items</strong></td>
                <td>Pending tasks requiring immediate action</td>
            </tr>
            <tr>
                <td><strong>Charts</strong></td>
                <td>Visual representation of enrollment, fees, attendance, and performance</td>
            </tr>
            <tr>
                <td><strong>Upcoming Events</strong></td>
                <td>Calendar of scheduled school events</td>
            </tr>
        </table>
    </div>

    <h2>1.3 Navigation</h2>
    <div class="section">
        <p>The left sidebar provides access to all system modules. Click on any menu item to access that section.</p>

        <div class="info-box">
            <strong>Sidebar Features:</strong>
            <ul>
                <li>Click the collapse button to minimize the sidebar</li>
                <li>Active menu items are highlighted in red</li>
                <li>Grouped items can be expanded/collapsed</li>
            </ul>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 2: Dashboard Widgets -->
    <h1>2. Dashboard Widgets Explained</h1>

    <h2>2.1 Statistics Cards</h2>
    <div class="section">
        <p>The statistics cards provide at-a-glance metrics for key school data:</p>

        <div class="widget-card">
            <div class="widget-title">Total Students</div>
            <p>Shows the total number of currently enrolled students. The subtitle indicates how many are active in the current term.</p>
        </div>

        <div class="widget-card">
            <div class="widget-title">Total Teachers</div>
            <p>Displays the count of teaching staff. The subtitle shows full-time vs part-time breakdown.</p>
        </div>

        <div class="widget-card">
            <div class="widget-title">Fees Collection</div>
            <p>Shows total fees collected for the current term. The subtitle indicates the collection percentage.</p>
        </div>

        <div class="widget-card">
            <div class="widget-title">Attendance Rate</div>
            <p>Today's attendance percentage across all classes. Green indicates good attendance (>80%).</p>
        </div>

        <div class="widget-card">
            <div class="widget-title">Active Homework</div>
            <p>Number of currently active homework assignments. Shows pending submissions count.</p>
        </div>

        <div class="widget-card">
            <div class="widget-title">Upcoming Events</div>
            <p>Count of scheduled events in the next 30 days.</p>
        </div>
    </div>

    <h2>2.2 Charts & Graphs</h2>
    <div class="section">
        <table>
            <tr>
                <th>Chart</th>
                <th>What It Shows</th>
                <th>How to Interpret</th>
            </tr>
            <tr>
                <td><strong>Enrollment by Grade</strong></td>
                <td>Distribution of students across grades</td>
                <td>Larger segments indicate more students in that grade. Hover for exact numbers.</td>
            </tr>
            <tr>
                <td><strong>Fee Collection Trend</strong></td>
                <td>Monthly fee collection over time</td>
                <td>Rising trend indicates improving collections. Dips may need attention.</td>
            </tr>
            <tr>
                <td><strong>Today's Attendance</strong></td>
                <td>Breakdown of attendance status</td>
                <td>Shows Present, Absent, Late, and Excused counts. Aim for >90% present.</td>
            </tr>
            <tr>
                <td><strong>Top Subjects</strong></td>
                <td>Average performance by subject</td>
                <td>Longer bars indicate better class averages. Identify subjects needing improvement.</td>
            </tr>
        </table>
    </div>

    <h2>2.3 Attention Items</h2>
    <div class="section">
        <p>The "Items Requiring Attention" section highlights pending tasks:</p>

        <table>
            <tr>
                <th>Item</th>
                <th>What It Means</th>
                <th>Action Required</th>
            </tr>
            <tr>
                <td><strong>Ungraded Submissions</strong></td>
                <td>Homework submissions awaiting grades</td>
                <td>Go to Homework > View submissions and grade them</td>
            </tr>
            <tr>
                <td><strong>Overdue Homework</strong></td>
                <td>Assignments past their due date</td>
                <td>Review and either extend deadline or close assignment</td>
            </tr>
            <tr>
                <td><strong>Overdue Fees</strong></td>
                <td>Students with unpaid fees past due date</td>
                <td>Send reminders or review fee records</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- Section 3: Student Management -->
    <h1>3. Student Management</h1>

    <h2>3.1 Adding New Students</h2>
    <div class="section">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Navigate to <strong>Students</strong> in the sidebar</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Click the <strong>"New Student"</strong> button (top right)</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Fill in the required fields:
                <ul>
                    <li>Student Name (First, Middle, Last)</li>
                    <li>Date of Birth</li>
                    <li>Gender</li>
                    <li>Guardian/Parent Information</li>
                    <li>Contact Details</li>
                </ul>
            </div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Upload student photo (optional but recommended)</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Click <strong>"Create"</strong> to save the student record</div>
        </div>

        <div class="warning-box">
            <strong>Important:</strong> A Student ID will be automatically generated. Make sure guardian contact information is accurate for communication purposes.
        </div>
    </div>

    <h2>3.2 Managing Enrollments</h2>
    <div class="section">
        <p>After creating a student, enroll them in a class:</p>

        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Open the student's record</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Go to the <strong>Enrollments</strong> tab</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Click <strong>"New Enrollment"</strong></div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Select the Academic Year, Term, Grade, and Section</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Set enrollment status to "Active"</div>
        </div>
        <div class="step">
            <div class="step-number">6</div>
            <div class="step-content">Save the enrollment</div>
        </div>
    </div>

    <h2>3.3 Student Records</h2>
    <div class="section">
        <p>Each student record contains multiple tabs:</p>
        <ul>
            <li><strong>Details:</strong> Personal and contact information</li>
            <li><strong>Enrollments:</strong> Academic history and current enrollment</li>
            <li><strong>Attendance:</strong> Daily attendance records</li>
            <li><strong>Results:</strong> Academic performance and grades</li>
            <li><strong>Fees:</strong> Payment history and balances</li>
            <li><strong>Documents:</strong> Uploaded certificates and files</li>
        </ul>
    </div>

    <div class="page-break"></div>

    <!-- Section 4: Academic Management -->
    <h1>4. Academic Management</h1>

    <h2>4.1 Classes & Sections</h2>
    <div class="section">
        <p><strong>Setting up Classes:</strong></p>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Go to <strong>Grades</strong> to define grade levels (e.g., Grade 1-12)</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Go to <strong>Class Sections</strong> to create sections (e.g., Grade 5A, Grade 5B)</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Assign a class teacher to each section</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Set the maximum capacity for each section</div>
        </div>
    </div>

    <h2>4.2 Subjects & Curriculum</h2>
    <div class="section">
        <p><strong>Managing Subjects:</strong></p>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Navigate to <strong>Subjects</strong> in the sidebar</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Click <strong>"New Subject"</strong></div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Enter subject name, code, and type (Core/Elective)</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Assign to applicable grades</div>
        </div>

        <div class="info-box">
            <strong>Subject Types:</strong>
            <ul>
                <li><strong>Core:</strong> Mandatory subjects (e.g., Mathematics, English)</li>
                <li><strong>Elective:</strong> Optional subjects students can choose</li>
            </ul>
        </div>
    </div>

    <h2>4.3 Entering Results</h2>
    <div class="section">
        <p><strong>Recording Student Grades:</strong></p>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Go to <strong>Enter Results</strong> from Quick Actions or sidebar</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Select the Academic Year, Term, Grade, and Subject</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Enter marks for each assessment component (CA, Exams, etc.)</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">The system automatically calculates totals and grades</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Click <strong>"Save Results"</strong></div>
        </div>

        <div class="tip-box">
            <strong>Tip:</strong> You can import results from Excel using the Import feature for bulk entry.
        </div>
    </div>

    <h2>4.4 Generating Report Cards</h2>
    <div class="section">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Navigate to <strong>Report Cards</strong> from Quick Actions</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Select the Academic Year, Term, and Grade</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Choose students (individual or bulk)</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Click <strong>"Generate"</strong> to create PDF report cards</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Download or print the generated reports</div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 5: Attendance -->
    <h1>5. Attendance Tracking</h1>

    <h2>5.1 Marking Attendance</h2>
    <div class="section">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Go to <strong>Mark Attendance</strong> from the Quick Actions</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Select the Date, Grade, and Section</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Mark each student as:
                <ul>
                    <li><strong>Present (P):</strong> Student attended class</li>
                    <li><strong>Absent (A):</strong> Student did not attend</li>
                    <li><strong>Late (L):</strong> Student arrived after start time</li>
                    <li><strong>Excused (E):</strong> Absent with valid reason</li>
                </ul>
            </div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Add notes for absences if needed</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Click <strong>"Save Attendance"</strong></div>
        </div>

        <div class="warning-box">
            <strong>Note:</strong> Attendance should be marked daily, preferably at the start of each school day.
        </div>
    </div>

    <h2>5.2 Attendance Reports</h2>
    <div class="section">
        <p>Generate attendance reports to monitor patterns:</p>
        <ul>
            <li><strong>Daily Report:</strong> Overview of attendance for a specific day</li>
            <li><strong>Student Report:</strong> Individual student's attendance history</li>
            <li><strong>Class Report:</strong> Attendance summary for a class over a period</li>
            <li><strong>Monthly Report:</strong> Month-wise attendance statistics</li>
        </ul>

        <div class="info-box">
            <strong>Understanding Attendance Rate:</strong>
            <ul>
                <li><strong>90-100%:</strong> Excellent attendance (Green)</li>
                <li><strong>80-89%:</strong> Good attendance (Blue)</li>
                <li><strong>70-79%:</strong> Needs improvement (Orange)</li>
                <li><strong>Below 70%:</strong> Concerning - requires intervention (Red)</li>
            </ul>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 6: Fee Management -->
    <h1>6. Fee Management</h1>

    <h2>6.1 Fee Structures</h2>
    <div class="section">
        <p>Set up fee structures before recording payments:</p>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Go to <strong>Fee Structures</strong> in the sidebar</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Create fee types (Tuition, Transport, Meals, etc.)</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Set amounts for each grade level</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Configure payment schedules (Termly, Monthly, Annual)</div>
        </div>
    </div>

    <h2>6.2 Recording Payments</h2>
    <div class="section">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Navigate to <strong>Fee Payments</strong></div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Click <strong>"New Payment"</strong></div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Select the student</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Enter payment amount and method (Cash, Bank, Mobile Money)</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Add receipt number if applicable</div>
        </div>
        <div class="step">
            <div class="step-number">6</div>
            <div class="step-content">Save and optionally print receipt</div>
        </div>
    </div>

    <h2>6.3 Generating Invoices</h2>
    <div class="section">
        <p>Create invoices for fee collection:</p>
        <ul>
            <li>Go to <strong>Invoices</strong> section</li>
            <li>Select students or generate bulk invoices by class</li>
            <li>Choose fee items to include</li>
            <li>Set due date</li>
            <li>Generate and distribute to parents</li>
        </ul>

        <div class="tip-box">
            <strong>Tip:</strong> Use the Fee Generation Widget on the dashboard to quickly generate bulk invoices for a term.
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 7: Homework -->
    <h1>7. Homework & Assignments</h1>

    <div class="section">
        <h2>Creating Homework</h2>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Go to <strong>Homework</strong> in the sidebar</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Click <strong>"New Homework"</strong></div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Fill in details:
                <ul>
                    <li>Title and description</li>
                    <li>Subject and class</li>
                    <li>Due date</li>
                    <li>Attach files if needed</li>
                </ul>
            </div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Publish to make visible to students/parents</div>
        </div>

        <h2>Grading Submissions</h2>
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">Open the homework assignment</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">Go to <strong>Submissions</strong> tab</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">Review each submission</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">Enter grade and feedback</div>
        </div>
        <div class="step">
            <div class="step-number">5</div>
            <div class="step-content">Save to notify the student</div>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 8: Reports -->
    <h1>8. Reports & Analytics</h1>

    <div class="section">
        <p>The system provides various reports for analysis and decision-making:</p>

        <table>
            <tr>
                <th>Report Type</th>
                <th>Purpose</th>
                <th>How to Access</th>
            </tr>
            <tr>
                <td><strong>Student List</strong></td>
                <td>Complete list of students with filters</td>
                <td>Students > Export</td>
            </tr>
            <tr>
                <td><strong>Attendance Summary</strong></td>
                <td>Attendance statistics by class/period</td>
                <td>Attendance Reports</td>
            </tr>
            <tr>
                <td><strong>Fee Collection</strong></td>
                <td>Payment status and outstanding balances</td>
                <td>Fees > Reports</td>
            </tr>
            <tr>
                <td><strong>Academic Performance</strong></td>
                <td>Grade analysis by subject/class</td>
                <td>Results > Analysis</td>
            </tr>
            <tr>
                <td><strong>Report Cards</strong></td>
                <td>Individual student progress reports</td>
                <td>Report Cards</td>
            </tr>
            <tr>
                <td><strong>Teacher Reports</strong></td>
                <td>Teaching assignments and schedules</td>
                <td>Teachers > Reports</td>
            </tr>
        </table>

        <div class="info-box">
            <strong>Export Options:</strong> Most reports can be exported to PDF or Excel format for printing or further analysis.
        </div>
    </div>

    <div class="page-break"></div>

    <!-- Section 9: Quick Reference -->
    <h1>9. Quick Reference</h1>

    <h2>Keyboard Shortcuts</h2>
    <table>
        <tr>
            <th>Action</th>
            <th>Shortcut</th>
        </tr>
        <tr>
            <td>Global Search</td>
            <td>Ctrl + K (or Cmd + K on Mac)</td>
        </tr>
        <tr>
            <td>Save Form</td>
            <td>Ctrl + S</td>
        </tr>
        <tr>
            <td>Navigate Back</td>
            <td>Browser Back Button</td>
        </tr>
    </table>

    <h2>Common Tasks Quick Steps</h2>
    <table>
        <tr>
            <th>Task</th>
            <th>Quick Steps</th>
        </tr>
        <tr>
            <td>Add new student</td>
            <td>Students > New Student > Fill form > Create</td>
        </tr>
        <tr>
            <td>Mark attendance</td>
            <td>Dashboard > Mark Attendance > Select class > Mark > Save</td>
        </tr>
        <tr>
            <td>Record payment</td>
            <td>Fee Payments > New Payment > Select student > Enter amount > Save</td>
        </tr>
        <tr>
            <td>Enter results</td>
            <td>Enter Results > Select class/subject > Enter marks > Save</td>
        </tr>
        <tr>
            <td>Generate report card</td>
            <td>Report Cards > Select criteria > Generate > Download</td>
        </tr>
        <tr>
            <td>Create homework</td>
            <td>Homework > New > Fill details > Publish</td>
        </tr>
    </table>

    <h2>Status Color Codes</h2>
    <table>
        <tr>
            <th>Color</th>
            <th>Meaning</th>
        </tr>
        <tr>
            <td style="background: #dcfce7; color: #166534;">Green</td>
            <td>Success, Active, Good standing</td>
        </tr>
        <tr>
            <td style="background: #fef9c3; color: #854d0e;">Yellow/Amber</td>
            <td>Pending, Warning, Needs attention</td>
        </tr>
        <tr>
            <td style="background: #fee2e2; color: #991b1b;">Red</td>
            <td>Error, Overdue, Critical</td>
        </tr>
        <tr>
            <td style="background: #dbeafe; color: #1e40af;">Blue</td>
            <td>Information, In progress</td>
        </tr>
        <tr>
            <td style="background: #f3f4f6; color: #374151;">Gray</td>
            <td>Inactive, Disabled, Archived</td>
        </tr>
    </table>

    <h2>Support & Help</h2>
    <div class="info-box">
        <p><strong>For technical support, please contact:</strong></p>
        <ul>
            <li>System Administrator</li>
            <li>Email: support@stfrancisschool.com</li>
            <li>Phone: Contact school office</li>
        </ul>
    </div>

    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 10px;">
        <p>---</p>
        <p><strong>St. Francis of Assisi Private School</strong></p>
        <p>"For God and For Country"</p>
        <p>School Portal Quick Guide v1.0 | {{ now()->format('F Y') }}</p>
    </div>

</body>
</html>
