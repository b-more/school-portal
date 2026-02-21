<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\SchoolSettings;
use App\Models\Student;
use App\Models\Term;
use App\Exports\AttendanceRegisterExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceRegisterController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
        ]);

        $classSectionId = $request->input('class_section_id');

        // Get active term
        $term = Term::current();
        if (!$term) {
            abort(404, 'No active term found. Please set an active term first.');
        }

        $classSection = ClassSection::with(['grade', 'classTeacher'])->findOrFail($classSectionId);
        $schoolSettings = SchoolSettings::getInstance();

        // Compute Week 1 Monday from term start date
        $termStart = Carbon::parse($term->start_date);
        $week1Monday = $termStart->copy()->startOfWeek(Carbon::MONDAY);

        // Build 13 weeks × 5 days (Mon–Fri)
        $weeks = [];
        for ($w = 0; $w < 13; $w++) {
            $weekDays = [];
            for ($d = 0; $d < 5; $d++) {
                $weekDays[] = $week1Monday->copy()->addWeeks($w)->addDays($d);
            }
            $weeks[] = $weekDays;
        }

        $firstDate = $weeks[0][0]->toDateString();
        $lastDate = $weeks[12][4]->toDateString();

        // Query ALL attendance for this class section within the term range
        $attendanceRecords = Attendance::where('class_section_id', $classSectionId)
            ->whereBetween('attendance_date', [$firstDate, $lastDate])
            ->get()
            ->groupBy(function ($record) {
                return $record->student_id . '-' . $record->attendance_date->format('Y-m-d');
            });

        // Load students with parent info
        $students = Student::with('parentGuardian')
            ->where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

        // Build student matrix
        $studentMatrix = [];
        foreach ($students as $student) {
            $days = [];
            $totals = ['P' => 0, 'X' => 0, 'S' => 0, 'Y' => 0, 'L' => 0];

            foreach ($weeks as $weekDays) {
                foreach ($weekDays as $date) {
                    $key = $student->id . '-' . $date->format('Y-m-d');
                    if (isset($attendanceRecords[$key])) {
                        $record = $attendanceRecords[$key]->first();
                        $symbol = Attendance::getStatusSymbol($record->status);
                        $days[$date->format('Y-m-d')] = $symbol;
                        if (isset($totals[$symbol])) {
                            $totals[$symbol]++;
                        }
                    } else {
                        $days[$date->format('Y-m-d')] = '-';
                    }
                }
            }

            $studentMatrix[] = [
                'name' => $student->name,
                'dob' => $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : '-',
                'gender' => $student->gender ? strtoupper(substr($student->gender, 0, 1)) : '-',
                'parent_name' => $student->parentGuardian->name ?? '-',
                'parent_phone' => $student->parentGuardian->phone ?? '-',
                'days' => $days,
                'totals' => $totals,
            ];
        }

        $className = ($classSection->grade->name ?? 'N/A') . ' - ' . $classSection->name;
        $gradeTeacher = $classSection->classTeacher->name ?? '-';

        $data = [
            'schoolSettings' => $schoolSettings,
            'classSection' => $classSection,
            'className' => $className,
            'gradeTeacher' => $gradeTeacher,
            'term' => $term,
            'weeks' => $weeks,
            'students' => $studentMatrix,
            'totalStudents' => count($students),
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdf.attendance-daily-register', $data);
        $pdf->setPaper('A4', 'landscape');

        $gradeName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $classSection->grade->name ?? 'Unknown'));
        $sectionName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $classSection->name));
        $termName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $term->name));
        $filename = "Termly-Register-{$gradeName}-{$sectionName}-{$termName}.pdf";

        return $pdf->download($filename);
    }

    public function downloadExcel(Request $request)
    {
        $request->validate([
            'class_section_id' => 'required|integer|exists:class_sections,id',
        ]);

        $classSectionId = $request->input('class_section_id');

        $term = Term::current();
        if (!$term) {
            abort(404, 'No active term found. Please set an active term first.');
        }

        $classSection = ClassSection::with('grade')->findOrFail($classSectionId);

        $gradeName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $classSection->grade->name ?? 'Unknown'));
        $sectionName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $classSection->name));
        $termName = preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $term->name));
        $filename = "Termly-Register-{$gradeName}-{$sectionName}-{$termName}.xlsx";

        return Excel::download(new AttendanceRegisterExport($classSectionId), $filename);
    }
}
