<?php

namespace App\Filament\Resources\AttendanceResource\Widgets;

use App\Models\Attendance;
use App\Models\ClassSection;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;


class AttendanceDailyRegister extends Widget
{
    protected static string $view = 'filament.resources.attendance-resource.widgets.attendance-daily-register';

    protected int | string | array $columnSpan = 'full';

    public string $selectedDate = '';

    public ?int $selectedClassSectionId = null;

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function selectClass(int $classSectionId): void
    {
        if ($this->selectedClassSectionId === $classSectionId) {
            $this->selectedClassSectionId = null;
        } else {
            $this->selectedClassSectionId = $classSectionId;
        }
    }

    public function getClassStudents(): array
    {
        if (!$this->selectedClassSectionId) {
            return [];
        }

        $date = $this->selectedDate;
        $classSectionId = $this->selectedClassSectionId;

        // Get all active students with parent info
        $students = Student::with('parentGuardian')
            ->where('class_section_id', $classSectionId)
            ->where('enrollment_status', 'active')
            ->orderBy('name')
            ->get();

        // Get attendance records for this date and class section
        $attendanceRecords = Attendance::where('attendance_date', $date)
            ->where('class_section_id', $classSectionId)
            ->get()
            ->keyBy('student_id');

        $result = [];
        foreach ($students as $student) {
            $attendance = $attendanceRecords->get($student->id);
            $statusKey = $attendance ? $attendance->status : 'not_marked';
            $symbol = $attendance ? Attendance::getStatusSymbol($attendance->status) : '-';

            $result[] = [
                'student_name' => $student->name,
                'date_of_birth' => $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : '-',
                'gender' => $student->gender ? strtoupper(substr($student->gender, 0, 1)) : '-',
                'parent_name' => $student->parentGuardian->name ?? '-',
                'parent_phone' => $student->parentGuardian->phone ?? '-',
                'status' => $symbol,
                'status_key' => $statusKey,
            ];
        }

        return $result;
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $next = Carbon::parse($this->selectedDate)->addDay();

        if ($next->lte(now()->endOfDay())) {
            $this->selectedDate = $next->toDateString();
        }
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function updatedSelectedDate(): void
    {
        // Prevent future dates
        if (Carbon::parse($this->selectedDate)->gt(now()->endOfDay())) {
            $this->selectedDate = now()->toDateString();
        }

        // Close detail panel on date change
        $this->selectedClassSectionId = null;
    }

    public function getRegisterData(): array
    {
        $date = $this->selectedDate;

        // Get all active class sections with grade, ordered by grade level
        $classSections = ClassSection::with(['grade', 'classTeacher'])
            ->where('is_active', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->grade->level ?? 0) <=> ($b->grade->level ?? 0),
                fn ($a, $b) => strcmp($a->name, $b->name),
            ]);

        // Count active students per class section
        $studentCounts = Student::where('enrollment_status', 'active')
            ->whereNotNull('class_section_id')
            ->select('class_section_id', DB::raw('COUNT(*) as total'))
            ->groupBy('class_section_id')
            ->pluck('total', 'class_section_id')
            ->toArray();

        // Get attendance records for the selected date, grouped by class_section_id and status
        $attendanceData = Attendance::where('attendance_date', $date)
            ->select('class_section_id', 'status', DB::raw('COUNT(*) as count'))
            ->groupBy('class_section_id', 'status')
            ->get();

        // Build a lookup: class_section_id => [status => count]
        $attendanceLookup = [];
        foreach ($attendanceData as $row) {
            $attendanceLookup[$row->class_section_id][$row->status] = $row->count;
        }

        $register = [];
        $totalClasses = 0;
        $fullyMarked = 0;
        $partiallyMarked = 0;
        $notStarted = 0;

        foreach ($classSections as $cs) {
            $totalStudents = $studentCounts[$cs->id] ?? 0;
            $statuses = $attendanceLookup[$cs->id] ?? [];

            $present = $statuses['present'] ?? 0;
            $absent = $statuses['absent'] ?? 0;
            $sick = $statuses['sick'] ?? 0;
            $late = $statuses['late'] ?? 0;
            $excused = $statuses['excused'] ?? 0;
            $markedCount = $present + $absent + $sick + $late + $excused;

            $percentMarked = $totalStudents > 0 ? round(($markedCount / $totalStudents) * 100) : 0;

            if ($totalStudents === 0) {
                $statusLabel = 'No Students';
                $statusColor = 'gray';
            } elseif ($percentMarked >= 100) {
                $statusLabel = 'Complete';
                $statusColor = 'green';
                $fullyMarked++;
            } elseif ($percentMarked > 0) {
                $statusLabel = 'Partial';
                $statusColor = 'yellow';
                $partiallyMarked++;
            } else {
                $statusLabel = 'Not Started';
                $statusColor = 'gray';
                $notStarted++;
            }

            if ($totalStudents > 0) {
                $totalClasses++;
            }

            $register[] = [
                'class_section_id' => $cs->id,
                'class_name' => ($cs->grade->name ?? 'N/A') . ' - ' . $cs->name,
                'grade_teacher' => $cs->classTeacher->name ?? '-',
                'total_students' => $totalStudents,
                'present' => $present,
                'absent' => $absent,
                'sick' => $sick,
                'late' => $late,
                'excused' => $excused,
                'marked_count' => $markedCount,
                'percent_marked' => $percentMarked,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
            ];
        }

        return [
            'register' => $register,
            'summary' => [
                'total_classes' => $totalClasses,
                'fully_marked' => $fullyMarked,
                'partially_marked' => $partiallyMarked,
                'not_started' => $notStarted,
            ],
            'date_display' => Carbon::parse($date)->format('l, j F Y'),
            'is_today' => Carbon::parse($date)->isToday(),
        ];
    }

    protected function getViewData(): array
    {
        $registerData = $this->getRegisterData();

        $selectedClassInfo = null;
        if ($this->selectedClassSectionId) {
            $cs = ClassSection::with(['grade', 'classTeacher'])->find($this->selectedClassSectionId);
            if ($cs) {
                $selectedClassInfo = [
                    'class_name' => ($cs->grade->name ?? 'N/A') . ' - ' . $cs->name,
                    'grade_teacher' => $cs->classTeacher->name ?? '-',
                    'student_count' => Student::where('class_section_id', $cs->id)
                        ->where('enrollment_status', 'active')
                        ->count(),
                ];
            }
        }

        return [
            'data' => $registerData,
            'classStudents' => $this->getClassStudents(),
            'selectedClassInfo' => $selectedClassInfo,
        ];
    }
}
