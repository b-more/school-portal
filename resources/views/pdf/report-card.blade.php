<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $student->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }

        .container {
            width: 100%;
            max-width: 180mm;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            text-align: center;
            border-bottom: 3px double #1e40af;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .school-logo {
            width: 65px;
            height: 65px;
            margin: 0 auto 8px auto;
            display: block;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .school-motto {
            font-style: italic;
            color: #666;
            margin: 2px 0;
            font-size: 9px;
        }

        .school-contact {
            font-size: 8px;
            color: #555;
            margin-top: 3px;
        }

        .report-title {
            background: #1e40af;
            color: white;
            padding: 6px 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 10px 0;
            text-align: center;
        }

        /* Student Info Section */
        .student-info {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            background: #f8fafc;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            border-right: 1px solid #eee;
        }

        .info-cell:last-child {
            border-right: none;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            font-size: 8px;
            text-transform: uppercase;
        }

        .info-value {
            font-size: 10px;
            color: #1e40af;
            font-weight: 600;
        }

        /* Results Table */
        table.results {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.results th {
            background: #1e40af;
            color: white;
            padding: 6px 5px;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }

        table.results th:first-child {
            text-align: left;
            padding-left: 8px;
        }

        table.results td {
            padding: 5px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
        }

        table.results td:first-child {
            text-align: left;
            padding-left: 8px;
            font-weight: 500;
        }

        table.results tr:nth-child(even) {
            background: #f9fafb;
        }

        .grade-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }

        .grade-a { background: #dcfce7; color: #166534; }
        .grade-b { background: #dbeafe; color: #1e40af; }
        .grade-c { background: #fef9c3; color: #854d0e; }
        .grade-d { background: #fed7aa; color: #c2410c; }
        .grade-e { background: #fee2e2; color: #b91c1c; }
        .grade-f { background: #fecaca; color: #991b1b; }

        /* Summary Section */
        .summary-box {
            display: table;
            width: 100%;
            border: 2px solid #1e40af;
            margin-bottom: 10px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 8px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
        }

        .summary-cell:last-child {
            border-right: none;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #1e40af;
        }

        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Grading Scale */
        .grading-scale {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            margin-bottom: 10px;
            font-size: 8px;
        }

        .grading-scale-title {
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .scale-item {
            color: #555;
        }

        .scale-item strong {
            color: #1e40af;
        }

        /* Comments Section */
        .comment-box {
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 8px;
            min-height: 45px;
            background: #fff;
        }

        .comment-label {
            font-weight: bold;
            font-size: 8px;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 3px;
            border-bottom: 1px dashed #ddd;
            padding-bottom: 2px;
        }

        .comment-text {
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }

        .comment-placeholder {
            color: #999;
            font-style: italic;
        }

        /* Signatures Section */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .signature-row {
            display: table-row;
        }

        .signature-cell {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 25px;
            padding-top: 4px;
        }

        .signature-name {
            font-size: 9px;
            font-weight: bold;
            color: #333;
        }

        .signature-title {
            font-size: 8px;
            color: #666;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 2px solid #1e40af;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .date-issued {
            font-weight: bold;
            color: #333;
        }

        /* Print styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Centered Logo -->
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="school-logo">
            <div class="school-name">{{ $schoolSettings->school_name ?? 'School Name' }}</div>
            <div class="school-contact">
                {{ $schoolSettings->address ?? '' }}{{ ($schoolSettings->city ?? null) ? ', ' . $schoolSettings->city : '' }}{{ ($schoolSettings->country ?? null) ? ', ' . $schoolSettings->country : '' }}
                <br>
                {{ ($schoolSettings->phone ?? null) ? 'Phone: ' . $schoolSettings->phone : '' }}{{ ($schoolSettings->email ?? null) ? ' | Email: ' . $schoolSettings->email : '' }}
            </div>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            Student Report Card - {{ $term->name ?? 'Term' }} {{ $year }}
        </div>

        <!-- Student Information (Simplified) -->
        <div class="student-info">
            <div class="info-row">
                <div class="info-cell" style="width: 35%;">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $student->name }}</div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ ucfirst($student->gender ?? 'N/A') }}</div>
                </div>
                <div class="info-cell" style="width: 25%;">
                    <div class="info-label">Class</div>
                    <div class="info-value">
                        @if($student->classSection && $student->classSection->grade)
                            {{ $student->classSection->grade->name }} - {{ $student->classSection->name }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="info-cell" style="width: 20%;">
                    <div class="info-label">Academic Year</div>
                    <div class="info-value">{{ $academicYear->name ?? $year }}</div>
                </div>
            </div>
        </div>

        <!-- Results Table (Without Mid-Term and Average columns) -->
        <table class="results">
            <thead>
                <tr>
                    <th style="width: 45%;">Subject</th>
                    <th style="width: 20%;">Marks (%)</th>
                    <th style="width: 15%;">Grade</th>
                    <th style="width: 20%;">Remark</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subjects = $resultsData['subjects'] ?? [];
                @endphp

                @forelse($subjects as $subject)
                    <tr>
                        <td>{{ $subject['subject_name'] }}</td>
                        <td><strong>{{ $subject['final'] !== null ? number_format($subject['final'], 0) : ($subject['combined'] !== null ? number_format($subject['combined'], 0) : '-') }}</strong></td>
                        <td>
                            @if(isset($subject['grade']) && $subject['grade'] !== 'N/A')
                                <span class="grade-badge grade-{{ strtolower($subject['grade']) }}">
                                    {{ $subject['grade'] }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $subject['remark'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No results available for this term</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary -->
        @php
            $combined = $resultsData['combined'] ?? ['average' => 0, 'total' => 0, 'subjects_count' => 0];
            $position = $resultsData['position'] ?? ['position' => null, 'total' => 0];
            $overallGrade = $resultsData['overall_grade'] ?? null;
            $average = $combined['average'] ?? 0;
            $positionNum = $position['position'] ?? null;
            $totalStudents = $position['total'] ?? 0;

            // Generate automated class teacher comment based on performance
            $classTeacherComments = [
                'excellent' => [
                    "Outstanding performance! {$student->name} has demonstrated exceptional academic abilities and consistently excels in all subjects. Keep up the excellent work!",
                    "Exceptional results! {$student->name} shows remarkable dedication to studies and maintains high standards across all subjects. A truly exemplary student.",
                    "{$student->name} has performed brilliantly this term. The consistent excellence in academics reflects strong commitment to learning. Well done!",
                ],
                'very_good' => [
                    "{$student->name} has shown very good performance this term. With continued effort and focus, even greater achievements are within reach.",
                    "Very good academic performance! {$student->name} demonstrates strong understanding across subjects and shows great potential for excellence.",
                    "A commendable performance by {$student->name}. The dedication shown this term is evident in the results. Keep striving for excellence!",
                ],
                'good' => [
                    "{$student->name} has performed well this term. There is good potential for improvement with more consistent effort and dedication.",
                    "Good progress shown by {$student->name}. With increased focus on weaker areas, better results can be achieved next term.",
                    "{$student->name} shows satisfactory performance. Encouraging more reading and practice will help achieve better grades.",
                ],
                'average' => [
                    "{$student->name} has shown average performance this term. More effort and attention to studies is needed for improvement.",
                    "There is room for improvement. {$student->name} should focus more on studies and seek help in challenging subjects.",
                    "{$student->name} needs to put in more effort. Regular study habits and completing assignments on time will help improve grades.",
                ],
                'below_average' => [
                    "{$student->name} needs significant improvement. Extra tutorials and more study time at home are strongly recommended.",
                    "Performance below expectations. {$student->name} must work harder and seek assistance from teachers in difficult subjects.",
                    "Urgent attention needed. {$student->name} should dedicate more time to studies and parents should monitor homework completion.",
                ],
            ];

            // Generate automated head teacher comment
            $headTeacherComments = [
                'excellent' => [
                    "Congratulations on this outstanding achievement! {$student->name} is a role model for other students. Continue to aim high!",
                    "Excellent performance that reflects hard work and dedication. We are proud of {$student->name}'s achievements.",
                    "Remarkable results! {$student->name} has shown what can be achieved through commitment and perseverance.",
                ],
                'very_good' => [
                    "Very good performance. {$student->name} has shown commendable effort. Keep working towards excellence.",
                    "Well done! {$student->name} continues to show strong academic capabilities. Maintain this positive trajectory.",
                    "Impressive results. With continued dedication, {$student->name} can achieve even greater success.",
                ],
                'good' => [
                    "Good effort this term. {$student->name} has potential for greater achievement with consistent application.",
                    "Satisfactory performance. We encourage {$student->name} to set higher goals and work towards them.",
                    "{$student->name} shows promise. More dedication to academics will yield better results.",
                ],
                'average' => [
                    "There is need for improvement. We encourage {$student->name} to be more focused and committed to studies.",
                    "{$student->name} should put in more effort. We recommend regular revision and completing all assignments.",
                    "We expect better performance next term. {$student->name} should work closely with teachers for improvement.",
                ],
                'below_average' => [
                    "Performance needs urgent attention. We request parents to closely monitor {$student->name}'s academic activities.",
                    "Significant improvement required. {$student->name} should attend extra classes and dedicate more time to studies.",
                    "We are concerned about this performance. A parent-teacher meeting is recommended to discuss {$student->name}'s progress.",
                ],
            ];

            // Determine performance category
            if ($average >= 80) {
                $category = 'excellent';
            } elseif ($average >= 65) {
                $category = 'very_good';
            } elseif ($average >= 50) {
                $category = 'good';
            } elseif ($average >= 40) {
                $category = 'average';
            } else {
                $category = 'below_average';
            }

            // Select random comment from category
            $autoClassTeacherComment = $classTeacherComments[$category][array_rand($classTeacherComments[$category])];
            $autoHeadTeacherComment = $headTeacherComments[$category][array_rand($headTeacherComments[$category])];
        @endphp

        <div class="summary-box">
            <div class="summary-row">
                <div class="summary-cell" style="width: 33%;">
                    <div class="summary-value">{{ number_format($combined['total'], 0) }}/{{ $combined['subjects_count'] * 100 }}</div>
                    <div class="summary-label">Total Marks</div>
                </div>
                <div class="summary-cell" style="width: 34%;">
                    <div class="summary-value">
                        @if($position['position'])
                            {{ $position['position'] }}/{{ $position['total'] }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="summary-label">Class Position</div>
                </div>
                <div class="summary-cell" style="width: 33%;">
                    <div class="summary-value">
                        @if($overallGrade)
                            <span class="grade-badge grade-{{ strtolower($overallGrade['grade']) }}">
                                {{ $overallGrade['grade'] }}
                            </span>
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="summary-label">Overall Grade</div>
                </div>
            </div>
        </div>

        <!-- Grading Scale -->
        @if($gradingScale)
            <div class="grading-scale">
                <span class="grading-scale-title">Grading Scale:</span>
                @foreach($gradingScale->items as $item)
                    <span class="scale-item">
                        <strong>{{ $item->grade }}:</strong> {{ $item->min_marks }}-{{ $item->max_marks }}%
                    </span>
                    @if(!$loop->last) | @endif
                @endforeach
            </div>
        @endif

        <!-- Comments Section -->
        <div class="comment-box">
            <div class="comment-label">Class Teacher's Comment</div>
            <div class="comment-text">
                {{ ($comments && $comments->class_teacher_comment) ? $comments->class_teacher_comment : $autoClassTeacherComment }}
            </div>
        </div>

        <div class="comment-box">
            <div class="comment-label">Head Teacher's Comment</div>
            <div class="comment-text">
                {{ ($comments && $comments->head_teacher_comment) ? $comments->head_teacher_comment : $autoHeadTeacherComment }}
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-row">
                <div class="signature-cell">
                    <div class="signature-line">
                        <div class="signature-name">{{ $classTeacherName ?? '________________' }}</div>
                        <div class="signature-title">Class Teacher</div>
                    </div>
                </div>
                <div class="signature-cell">
                    <div class="signature-line">
                        <div class="signature-name">Sylvester Lupando</div>
                        <div class="signature-title">Head Teacher</div>
                    </div>
                </div>
                <div class="signature-cell">
                    <div class="signature-line">
                        <div class="signature-name">Francis Mulenga</div>
                        <div class="signature-title">Executive Director</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <span class="date-issued">Date Issued: {{ $generatedAt->format('d F Y') }}</span>
        </div>
    </div>
</body>
</html>
