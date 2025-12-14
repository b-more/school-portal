<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\Term;
use App\Models\SchoolSection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentImportDataSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        $grade = Grade::first();
        $classSection = ClassSection::first();
        $term = Term::first();
        $schoolSection = SchoolSection::first();

        // Return example row
        return [
            [
                'John Banda',
                'STU-2025-001',
                'male',
                '2015-05-20',
                'Lusaka',
                '123 Main Street, Lusaka',
                $grade?->name ?? 'Grade 1',
                $classSection?->name ?? 'Grade 1 A',
                $schoolSection?->name ?? 'Primary',
                $term?->name ?? 'Term 1',
                'Primary',
                'active',
                '2025-01-15',
                'ABC Primary School',
                'Catholic',
                'No known allergies',
                'Additional notes',
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'student_id_number',
            'gender',
            'date_of_birth',
            'place_of_birth',
            'address',
            'grade_id',
            'class_section_id',
            'school_section',
            'enrollment_term_id',
            'standard_of_education',
            'enrollment_status',
            'admission_date',
            'previous_school',
            'religious_denomination',
            'medical_information',
            'notes',
        ];
    }

    public function title(): string
    {
        return 'Import Data';
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add border to all cells
        $sheet->getStyle('A1:Q2')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style example row (light background)
        $sheet->getStyle('A2:Q2')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280'],
            ],
        ]);

        // Add a note
        $sheet->setCellValue('A4', 'NOTE: Delete the example row above before importing. See "Reference Values" sheet for valid options.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'DC2626'],
            ],
        ]);

        return [];
    }
}
