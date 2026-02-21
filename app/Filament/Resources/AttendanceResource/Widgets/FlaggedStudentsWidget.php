<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class FlaggedStudentsWidget extends Widget
{
    protected static string $view = 'filament.resources.attendance-resource.widgets.flagged-students';

    protected int | string | array $columnSpan = 'full';

    public bool $expanded = false;

    protected int $threshold = 3;

    protected int $lookbackDays = 30;

    public function toggleExpanded(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function getFlaggedStudents(): array
    {
        $since = Carbon::now()->subDays($this->lookbackDays)->toDateString();
        $today = Carbon::now()->toDateString();

        // Get students with high absence/sick/excused counts in the last 30 days
        $flagged = Attendance::select(
                'student_id',
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick_count"),
                DB::raw("SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused_count"),
                DB::raw("SUM(CASE WHEN status IN ('absent', 'sick', 'excused') THEN 1 ELSE 0 END) as total_flagged"),
                DB::raw("MAX(CASE WHEN status IN ('absent', 'sick') THEN attendance_date ELSE NULL END) as last_absent_date")
            )
            ->whereBetween('attendance_date', [$since, $today])
            ->groupBy('student_id')
            ->havingRaw("SUM(CASE WHEN status IN ('absent', 'sick', 'excused') THEN 1 ELSE 0 END) >= ?", [$this->threshold])
            ->orderByDesc('total_flagged')
            ->limit(10)
            ->get();

        if ($flagged->isEmpty()) {
            return [];
        }

        // Load student details
        $studentIds = $flagged->pluck('student_id')->toArray();
        $students = \App\Models\Student::with(['classSection.grade'])
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');

        // Detect consecutive absent streaks for each flagged student
        $streaks = $this->getConsecutiveAbsentStreaks($studentIds, $since, $today);

        $results = [];
        foreach ($flagged as $row) {
            $student = $students->get($row->student_id);
            if (! $student) {
                continue;
            }

            $totalFlagged = $row->total_flagged;
            $severity = $totalFlagged >= 5 ? 'red' : 'orange';

            $className = 'N/A';
            if ($student->classSection) {
                $className = ($student->classSection->grade->name ?? '') . ' - ' . $student->classSection->name;
            }

            $results[] = [
                'student_name' => $student->name,
                'class_name' => $className,
                'absent_count' => $row->absent_count,
                'sick_count' => $row->sick_count,
                'excused_count' => $row->excused_count,
                'total_flagged' => $totalFlagged,
                'last_absent_date' => $row->last_absent_date
                    ? Carbon::parse($row->last_absent_date)->format('d M Y')
                    : '-',
                'streak' => $streaks[$row->student_id] ?? 0,
                'severity' => $severity,
            ];
        }

        return $results;
    }

    protected function getConsecutiveAbsentStreaks(array $studentIds, string $since, string $today): array
    {
        // Get all absent/sick records for these students, ordered by date desc
        $absences = Attendance::whereIn('student_id', $studentIds)
            ->whereIn('status', ['absent', 'sick'])
            ->whereBetween('attendance_date', [$since, $today])
            ->orderBy('student_id')
            ->orderByDesc('attendance_date')
            ->get(['student_id', 'attendance_date']);

        $streaks = [];
        $grouped = $absences->groupBy('student_id');

        foreach ($grouped as $studentId => $records) {
            $dates = $records->pluck('attendance_date')->map(fn ($d) => Carbon::parse($d))->values();

            if ($dates->isEmpty()) {
                $streaks[$studentId] = 0;

                continue;
            }

            // Count consecutive days from the most recent absence
            $streak = 1;
            for ($i = 1; $i < $dates->count(); $i++) {
                if ($dates[$i - 1]->diffInDays($dates[$i]) === 1) {
                    $streak++;
                } else {
                    break;
                }
            }

            $streaks[$studentId] = $streak;
        }

        return $streaks;
    }

    protected function getViewData(): array
    {
        $flagged = $this->getFlaggedStudents();

        return [
            'flaggedStudents' => $flagged,
            'flaggedCount' => count($flagged),
            'lookbackDays' => $this->lookbackDays,
        ];
    }
}
