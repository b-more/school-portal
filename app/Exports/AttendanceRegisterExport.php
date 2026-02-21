<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\SchoolSettings;
use App\Models\Student;
use App\Models\Term;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceRegisterExport implements WithMultipleSheets
{
    protected int $classSectionId;
    protected array $sharedData;

    public function __construct(int $classSectionId)
    {
        $this->classSectionId = $classSectionId;
        $this->sharedData = $this->buildData();
    }

    public function sheets(): array
    {
        return [
            new AttendanceRegisterStudentSheet($this->sharedData),
            new AttendanceRegisterGridSheet($this->sharedData),
        ];
    }

    protected function buildData(): array
    {
        $term = Term::current();
        $classSection = ClassSection::with(['grade', 'classTeacher'])->findOrFail($this->classSectionId);
        $schoolSettings = SchoolSettings::getInstance();

        $termStart = Carbon::parse($term->start_date);
        $week1Monday = $termStart->copy()->startOfWeek(Carbon::MONDAY);

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

        $attendanceRecords = Attendance::where('class_section_id', $this->classSectionId)
            ->whereBetween('attendance_date', [$firstDate, $lastDate])
            ->get()
            ->groupBy(fn($record) => $record->student_id . '-' . $record->attendance_date->format('Y-m-d'));

        $students = Student::with('parentGuardian')
            ->where('class_section_id', $this->classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

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
                'gender' => $student->gender ? strtoupper(substr($student->gender, 0, 1)) : '-',
                'days' => $days,
                'totals' => $totals,
            ];
        }

        $className = ($classSection->grade->name ?? 'N/A') . ' - ' . $classSection->name;

        return [
            'schoolName' => $schoolSettings->school_name ?? 'School',
            'className' => $className,
            'gradeTeacher' => $classSection->classTeacher->name ?? '-',
            'termName' => $term->name,
            'totalStudents' => count($students),
            'weeks' => $weeks,
            'students' => $studentMatrix,
        ];
    }
}

class AttendanceRegisterStudentSheet implements FromArray, WithTitle, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Student Information';
    }

    public function array(): array
    {
        $rows = [];

        // Row 1: School info
        $rows[] = [$this->data['schoolName'] . ' — TERMLY ATTENDANCE REGISTER'];
        // Row 2: Class info
        $rows[] = [
            'Term: ' . $this->data['termName'],
            'Class: ' . $this->data['className'],
            'Grade Teacher: ' . $this->data['gradeTeacher'],
            'Students: ' . $this->data['totalStudents'],
        ];
        // Row 3: blank
        $rows[] = [];
        // Row 4: headers
        $rows[] = ['#', 'Full Name', 'Gender'];
        // Row 5+: students
        foreach ($this->data['students'] as $i => $student) {
            $rows[] = [$i + 1, $student['name'], $student['gender']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $studentCount = count($this->data['students']);
        $lastRow = 4 + $studentCount;

        // Title row
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info row
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8EEF5'],
            ],
        ]);

        // Header row (row 4)
        $sheet->getStyle('A4:C4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows borders
        $sheet->getStyle("A4:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Alternating row colors
        for ($r = 5; $r <= $lastRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:C{$r}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F7F9FC'],
                    ],
                ]);
            }
        }

        // Center # and Gender columns
        $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("C5:C{$lastRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(10);

        return [];
    }
}

class AttendanceRegisterGridSheet implements FromArray, WithTitle, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Attendance Record';
    }

    public function array(): array
    {
        $rows = [];

        // Row 1: Title
        $rows[] = [$this->data['schoolName'] . ' — TERMLY ATTENDANCE REGISTER'];
        // Row 2: Class info
        $rows[] = [
            'Term: ' . $this->data['termName'],
            'Class: ' . $this->data['className'],
            'Grade Teacher: ' . $this->data['gradeTeacher'],
            'Students: ' . $this->data['totalStudents'],
        ];
        // Row 3: blank
        $rows[] = [];

        // Row 4: Week header row (#, Name, W1 x5, W2 x5, ... W13 x5, P, X, S, Y, L)
        $weekRow = ['', ''];
        for ($w = 0; $w < 13; $w++) {
            $weekRow[] = 'W' . ($w + 1);
            $weekRow[] = '';
            $weekRow[] = '';
            $weekRow[] = '';
            $weekRow[] = '';
        }
        $weekRow = array_merge($weekRow, ['P', 'X', 'S', 'Y', 'L']);
        $rows[] = $weekRow;

        // Row 5: Day header row (#, Name, M T W T F x 13, P X S Y L)
        $dayRow = ['#', 'Name'];
        for ($w = 0; $w < 13; $w++) {
            $dayRow = array_merge($dayRow, ['M', 'T', 'W', 'T', 'F']);
        }
        $dayRow = array_merge($dayRow, ['P', 'X', 'S', 'Y', 'L']);
        $rows[] = $dayRow;

        // Row 6+: Student data
        foreach ($this->data['students'] as $i => $student) {
            $row = [$i + 1, $student['name']];
            foreach ($this->data['weeks'] as $weekDays) {
                foreach ($weekDays as $date) {
                    $row[] = $student['days'][$date->format('Y-m-d')] ?? '-';
                }
            }
            $row[] = $student['totals']['P'] ?: '';
            $row[] = $student['totals']['X'] ?: '';
            $row[] = $student['totals']['S'] ?: '';
            $row[] = $student['totals']['Y'] ?: '';
            $row[] = $student['totals']['L'] ?: '';
            $rows[] = $row;
        }

        // Totals row
        $totalsRow = ['', 'DAILY TOTAL'];
        foreach ($this->data['weeks'] as $weekDays) {
            foreach ($weekDays as $date) {
                $dateStr = $date->format('Y-m-d');
                $count = 0;
                foreach ($this->data['students'] as $s) {
                    $sym = $s['days'][$dateStr] ?? '-';
                    if ($sym !== '-') $count++;
                }
                $totalsRow[] = $count ?: '';
            }
        }
        $grandP = $grandX = $grandS = $grandY = $grandL = 0;
        foreach ($this->data['students'] as $s) {
            $grandP += $s['totals']['P'];
            $grandX += $s['totals']['X'];
            $grandS += $s['totals']['S'];
            $grandY += $s['totals']['Y'];
            $grandL += $s['totals']['L'];
        }
        $totalsRow[] = $grandP ?: '';
        $totalsRow[] = $grandX ?: '';
        $totalsRow[] = $grandS ?: '';
        $totalsRow[] = $grandY ?: '';
        $totalsRow[] = $grandL ?: '';
        $rows[] = $totalsRow;

        // Legend row
        $rows[] = [];
        $rows[] = ['Legend:', '', 'P = Present', '', 'X = Absent', '', 'S = Sick', '', 'Y = Late', '', 'L = Excused', '', '- = Not Marked'];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $studentCount = count($this->data['students']);
        $lastDataRow = 5 + $studentCount;  // row 6 is first student, last student at 5+count
        $totalsRow = $lastDataRow + 1;
        $lastCol = 'BQ'; // 2 + 65 + 5 = 72 columns → BT... let me calculate
        // Col A=1, B=2, then 65 day cols (C to BO = cols 3-67), then 5 totals (BP-BT = 68-72)
        $lastCol = $this->colLetter(72);
        $lastDayCol = $this->colLetter(67);
        $totalStartCol = $this->colLetter(68);
        $totalEndCol = $this->colLetter(72);

        // Title row
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info row
        $sheet->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8EEF5'],
            ],
        ]);

        // Week header row (row 4): merge week labels across 5 columns each
        $sheet->getStyle("A4:{$lastCol}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        // Merge week header cells
        for ($w = 0; $w < 13; $w++) {
            $startCol = $this->colLetter(3 + $w * 5);
            $endCol = $this->colLetter(7 + $w * 5);
            $sheet->mergeCells("{$startCol}4:{$endCol}4");
        }

        // Day header row (row 5)
        $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '1E3A5F']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8EEF5'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Data area borders
        $sheet->getStyle("A6:{$lastCol}{$totalsRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '999999']],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['size' => 9],
        ]);

        // Name column left-align
        $sheet->getStyle("B6:B{$lastDataRow}")->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Totals row styling
        $sheet->getStyle("A{$totalsRow}:{$lastCol}{$totalsRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 9],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F0F0'],
            ],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                'bottom' => ['borderStyle' => Border::BORDER_THIN],
                'left' => ['borderStyle' => Border::BORDER_THIN],
                'right' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Color the status symbols in data cells
        $statusColors = [
            'P' => '065F46',
            'X' => '991B1B',
            'S' => '1E40AF',
            'Y' => '92400E',
            'L' => '5B21B6',
        ];

        // Color the total header cells (row 5)
        $totalLabels = ['P', 'X', 'S', 'Y', 'L'];
        for ($t = 0; $t < 5; $t++) {
            $col = $this->colLetter(68 + $t);
            $sheet->getStyle("{$col}5")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusColors[$totalLabels[$t]]]],
            ]);
        }

        // Color the total columns for each student row + totals row
        for ($t = 0; $t < 5; $t++) {
            $col = $this->colLetter(68 + $t);
            $sheet->getStyle("{$col}6:{$col}{$totalsRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusColors[$totalLabels[$t]]]],
            ]);
        }

        // Alternating row colors
        for ($r = 6; $r <= $lastDataRow; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FAFAFA'],
                    ],
                ]);
            }
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(4);   // #
        $sheet->getColumnDimension('B')->setWidth(28);  // Name
        for ($c = 3; $c <= 67; $c++) {
            $sheet->getColumnDimension($this->colLetter($c))->setWidth(3.5); // Day cells
        }
        for ($c = 68; $c <= 72; $c++) {
            $sheet->getColumnDimension($this->colLetter($c))->setWidth(5); // Total cells
        }

        // Freeze panes: freeze first 2 columns and header rows
        $sheet->freezePane('C6');

        return [];
    }

    protected function colLetter(int $num): string
    {
        $letter = '';
        while ($num > 0) {
            $num--;
            $letter = chr(65 + ($num % 26)) . $letter;
            $num = intdiv($num, 26);
        }
        return $letter;
    }
}
