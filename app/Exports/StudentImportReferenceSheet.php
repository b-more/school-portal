<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\Term;
use App\Models\SchoolSection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentImportReferenceSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        $grades = Grade::orderBy('name')->pluck('name')->toArray();
        // Get class sections with full names (Grade X A/B format)
        $classSections = ClassSection::select('class_sections.*')
            ->join('grades', 'class_sections.grade_id', '=', 'grades.id')
            ->orderBy('grades.id')
            ->orderBy('class_sections.name')
            ->get()
            ->map(fn($cs) => $cs->grade->name . ' ' . $cs->name)
            ->toArray();
        $terms = Term::orderBy('name')->pluck('name')->toArray();
        $schoolSections = SchoolSection::orderBy('order')->pluck('name')->toArray();

        $genderOptions = ['male', 'female'];
        $enrollmentStatusOptions = ['active', 'inactive', 'graduated', 'transferred'];
        $educationLevelOptions = ['Pre-School', 'Primary', 'Secondary'];

        // Find the maximum length to pad arrays
        $maxLength = max(
            count($grades),
            count($classSections),
            count($terms),
            count($schoolSections),
            count($genderOptions),
            count($enrollmentStatusOptions),
            count($educationLevelOptions)
        );

        $data = [];

        // Headers row
        $data[] = [
            'GRADES',
            'CLASS SECTIONS',
            'SCHOOL SECTIONS',
            'ENROLLMENT TERMS',
            'GENDER',
            'ENROLLMENT STATUS',
            'LEVEL OF EDUCATION',
        ];

        // Column descriptions
        $data[] = [
            '(use in grade_id)',
            '(use in class_section_id)',
            '(use in school_section)',
            '(use in enrollment_term_id)',
            '(use in gender)',
            '(use in enrollment_status)',
            '(use in standard_of_education)',
        ];

        // Empty row for spacing
        $data[] = ['', '', '', '', '', '', ''];

        // Data rows
        for ($i = 0; $i < $maxLength; $i++) {
            $data[] = [
                $grades[$i] ?? '',
                $classSections[$i] ?? '',
                $schoolSections[$i] ?? '',
                $terms[$i] ?? '',
                $genderOptions[$i] ?? '',
                $enrollmentStatusOptions[$i] ?? '',
                $educationLevelOptions[$i] ?? '',
            ];
        }

        return $data;
    }

    public function title(): string
    {
        return 'Reference Values';
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style the description row
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280'],
                'size' => 9,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D1FAE5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add borders to all data
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:G{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Alternate row colors for readability
        for ($i = 4; $i <= $highestRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:G{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }
        }

        return [];
    }
}
