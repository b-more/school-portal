<?php

namespace App\Exports;

use App\Models\Grade;
use App\Models\ClassSection;
use App\Models\Term;
use App\Models\SchoolSection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Import Data' => new StudentImportDataSheet(),
            'Reference Values' => new StudentImportReferenceSheet(),
        ];
    }
}
