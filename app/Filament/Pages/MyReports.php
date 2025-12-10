<?php

namespace App\Filament\Pages;

use App\Constants\RoleConstants;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Homework;
use App\Models\Result;
use App\Models\Attendance;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MyReports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.my-reports';
    protected static ?string $navigationLabel = 'My Reports';
    protected static ?string $navigationGroup = 'Teaching';
    protected static ?int $navigationSort = 3;

    public function getTeacher()
    {
        $user = Auth::user();
        return Teacher::where('user_id', $user->id)
            ->with(['subjectTeachings.classSection', 'grade', 'classSection'])
            ->first();
    }

    public function getClassSectionIds()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return [];
        }
        return $teacher->subjectTeachings->pluck('class_section_id')->unique()->toArray();
    }

    public function getStudentStats()
    {
        $classSectionIds = $this->getClassSectionIds();

        $total = Student::whereIn('class_section_id', $classSectionIds)
            ->where('enrollment_status', 'active')
            ->count();

        $boys = Student::whereIn('class_section_id', $classSectionIds)
            ->where('enrollment_status', 'active')
            ->where('gender', 'male')
            ->count();

        $girls = Student::whereIn('class_section_id', $classSectionIds)
            ->where('enrollment_status', 'active')
            ->where('gender', 'female')
            ->count();

        return [
            'total' => $total,
            'boys' => $boys,
            'girls' => $girls,
        ];
    }

    public function getHomeworkStats()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return ['total' => 0, 'pending' => 0, 'graded' => 0];
        }

        $total = Homework::where('assigned_by', $teacher->id)->count();
        $pending = Homework::where('assigned_by', $teacher->id)
            ->where('status', 'active')
            ->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'graded' => $total - $pending,
        ];
    }

    public function getAttendanceStats()
    {
        $classSectionIds = $this->getClassSectionIds();

        $today = Attendance::whereIn('class_section_id', $classSectionIds)
            ->whereDate('attendance_date', today())
            ->count();

        $thisWeek = Attendance::whereIn('class_section_id', $classSectionIds)
            ->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $thisMonth = Attendance::whereIn('class_section_id', $classSectionIds)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->count();

        return [
            'today' => $today,
            'week' => $thisWeek,
            'month' => $thisMonth,
        ];
    }

    public function getPerformanceByClass()
    {
        $teacher = $this->getTeacher();
        if (!$teacher) {
            return collect();
        }

        $classSectionIds = $this->getClassSectionIds();

        if (empty($classSectionIds)) {
            return collect();
        }

        return DB::table('results')
            ->join('students', 'results.student_id', '=', 'students.id')
            ->join('class_sections', 'students.class_section_id', '=', 'class_sections.id')
            ->join('grades', 'class_sections.grade_id', '=', 'grades.id')
            ->whereIn('students.class_section_id', $classSectionIds)
            ->select(
                'class_sections.id',
                'grades.name as grade_name',
                'class_sections.name as section_name',
                DB::raw('AVG(results.marks) as avg_marks'),
                DB::raw('COUNT(DISTINCT students.id) as student_count')
            )
            ->groupBy('class_sections.id', 'grades.name', 'class_sections.name')
            ->get();
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role_id, RoleConstants::teaching()) ?? false;
    }
}
