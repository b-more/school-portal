<?php

namespace App\Http\Controllers;

use App\Constants\RoleConstants;
use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $classSection = $request->get('class_section_id');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        $format = $request->get('format', 'html'); // html or csv

        // Build query with access control
        $query = Attendance::with(['student', 'classSection.grade', 'markedBy'])
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        // Apply role-based filtering
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $classSectionIds = $teacher->classSections()->pluck('class_sections.id')->toArray();
                $query->whereIn('class_section_id', $classSectionIds);
            }
        } elseif ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $query->where('student_id', $student->id);
            }
        }

        // Apply class section filter
        if ($classSection) {
            $query->where('class_section_id', $classSection);
        }

        $attendanceRecords = $query->orderBy('attendance_date', 'desc')
            ->orderBy('student_id')
            ->get();

        // Calculate statistics
        $totalRecords = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $lateCount = $attendanceRecords->where('status', 'late')->count();
        $excusedCount = $attendanceRecords->where('status', 'excused')->count();

        $statistics = [
            'total' => $totalRecords,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'present_percentage' => $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 2) : 0,
            'absent_percentage' => $totalRecords > 0 ? round(($absentCount / $totalRecords) * 100, 2) : 0,
        ];

        if ($format === 'csv') {
            return $this->exportCSV($attendanceRecords, $startDate, $endDate);
        }

        // Return HTML view for printing
        return view('attendance.report', [
            'records' => $attendanceRecords,
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'classSection' => $classSection ? ClassSection::find($classSection) : null,
        ]);
    }

    /**
     * Export attendance as CSV
     */
    protected function exportCSV($records, $startDate, $endDate)
    {
        $filename = 'attendance_report_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Date',
                'Student',
                'Class',
                'Status',
                'Check In',
                'Check Out',
                'Notes',
                'Marked By',
            ]);

            // CSV rows
            foreach ($records as $record) {
                fputcsv($file, [
                    $record->attendance_date->format('d/m/Y'),
                    $record->student->name,
                    $record->grade->name.' - '.$record->classSection->name,
                    ucfirst($record->status),
                    $record->check_in_time ? $record->check_in_time->format('H:i') : '-',
                    $record->check_out_time ? $record->check_out_time->format('H:i') : '-',
                    $record->notes ?? '-',
                    $record->markedBy->name ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
