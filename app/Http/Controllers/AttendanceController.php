<?php

namespace App\Http\Controllers;

use App\Constants\RoleConstants;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\SchoolSettings;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AttendanceController extends Controller
{
    /**
     * Export attendance report - Monthly Register Format
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $classSectionId = $request->get('class_section_id');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        $format = $request->get('format', 'pdf'); // pdf or csv

        if (!$classSectionId) {
            return back()->with('error', 'Please select a class');
        }

        $classSection = ClassSection::with('grade')->find($classSectionId);
        if (!$classSection) {
            return back()->with('error', 'Class not found');
        }

        // Check access
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $allowedSections = $teacher->classSections()->pluck('class_sections.id')->toArray();
                if (!in_array($classSectionId, $allowedSections)) {
                    return back()->with('error', 'Access denied');
                }
            }
        }

        // Get all students in this class
        $students = Student::where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

        // Get date range for the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        $daysInMonth = $endDate->day;

        // Get all attendance records for this class and month
        $attendanceRecords = Attendance::where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($record) {
                return $record->student_id . '-' . $record->attendance_date->format('Y-m-d');
            });

        // Build the attendance matrix
        $attendanceMatrix = [];
        $totals = [];

        foreach ($students as $student) {
            $studentData = [
                'id' => $student->id,
                'name' => $student->name,
                'student_id_number' => $student->student_id_number,
                'days' => [],
                'present' => 0,
                'absent' => 0,
                'sick' => 0,
                'late' => 0,
                'excused' => 0,
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                $key = $student->id . '-' . $date;

                if (isset($attendanceRecords[$key])) {
                    $record = $attendanceRecords[$key]->first();
                    $symbol = $this->getStatusCode($record->status);
                    $studentData['days'][$day] = $symbol;

                    match ($symbol) {
                        'P' => $studentData['present']++,
                        'X' => $studentData['absent']++,
                        'S' => $studentData['sick']++,
                        'Y' => $studentData['late']++,
                        'L' => $studentData['excused']++,
                        default => null,
                    };
                } else {
                    $studentData['days'][$day] = '-';
                }
            }

            $attendanceMatrix[] = $studentData;
        }

        // Get school settings
        $schoolSettings = SchoolSettings::getInstance();

        $data = [
            'schoolName' => $schoolSettings->school_name ?? 'School',
            'schoolLogo' => $schoolSettings->school_logo,
            'classSection' => $classSection,
            'month' => $startDate->format('F'),
            'year' => $year,
            'daysInMonth' => $daysInMonth,
            'students' => $attendanceMatrix,
            'reportDate' => now()->format('d/m/Y H:i'),
            'startDate' => $startDate,
        ];

        if ($format === 'csv') {
            return $this->exportMonthlyCSV($data);
        }

        // Return PDF/HTML view
        return view('pdf.attendance-register', $data);
    }

    /**
     * Get status code letter
     */
    protected function getStatusCode(string $status): string
    {
        return Attendance::getStatusSymbol($status);
    }

    /**
     * Export monthly attendance as CSV (matrix format)
     */
    protected function exportMonthlyCSV(array $data)
    {
        $filename = 'attendance_' . strtolower($data['month']) . '_' . $data['year'] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header row with dates
            $headerRow = ['#', 'Student Name'];
            for ($day = 1; $day <= $data['daysInMonth']; $day++) {
                $headerRow[] = $day;
            }
            $headerRow[] = 'P';
            $headerRow[] = 'X';
            $headerRow[] = 'S';
            $headerRow[] = 'Y';
            $headerRow[] = 'L';
            fputcsv($file, $headerRow);

            // Student rows
            $rowNum = 1;
            foreach ($data['students'] as $student) {
                $row = [$rowNum++, $student['name']];
                for ($day = 1; $day <= $data['daysInMonth']; $day++) {
                    $row[] = $student['days'][$day] ?? '-';
                }
                $row[] = $student['present'];
                $row[] = $student['absent'];
                $row[] = $student['sick'];
                $row[] = $student['late'];
                $row[] = $student['excused'];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
