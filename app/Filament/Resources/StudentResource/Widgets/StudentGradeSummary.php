<?php

namespace App\Filament\Resources\StudentResource\Widgets;

use App\Models\Grade;
use App\Models\Student;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class StudentGradeSummary extends Widget
{
    protected static string $view = 'filament.resources.student-resource.widgets.student-grade-summary';

    protected int | string | array $columnSpan = 'full';

    public function getGradeData(): array
    {
        $data = Student::where('enrollment_status', 'active')
            ->join('grades', 'students.grade_id', '=', 'grades.id')
            ->select(
                'grades.id as grade_id',
                'grades.name as grade_name',
                'grades.level',
                DB::raw("SUM(CASE WHEN students.gender = 'male' THEN 1 ELSE 0 END) as boys"),
                DB::raw("SUM(CASE WHEN students.gender = 'female' THEN 1 ELSE 0 END) as girls"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('grades.id', 'grades.name', 'grades.level')
            ->orderBy('grades.level')
            ->get()
            ->toArray();

        $totalBoys = array_sum(array_column($data, 'boys'));
        $totalGirls = array_sum(array_column($data, 'girls'));
        $totalAll = array_sum(array_column($data, 'total'));

        return [
            'grades' => $data,
            'totalBoys' => $totalBoys,
            'totalGirls' => $totalGirls,
            'totalAll' => $totalAll,
        ];
    }
}
