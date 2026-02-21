<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\SchoolSettings;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TimetableController extends Controller
{
    /**
     * Print/Stream class section timetable as PDF
     */
    public function printClassTimetable(Request $request, ClassSection $classSection, AcademicYear $academicYear)
    {
        $classSection->load(['grade', 'classTeacher']);

        $timetable = TimetableEntry::getClassTimetable($classSection->id, $academicYear->id);
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.class-timetable', [
            'classSection' => $classSection,
            'academicYear' => $academicYear,
            'timetable' => $timetable,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $filename = "timetable-{$classSection->grade->name}-{$classSection->name}-{$academicYear->name}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Download class section timetable as PDF
     */
    public function downloadClassTimetable(Request $request, ClassSection $classSection, AcademicYear $academicYear)
    {
        $classSection->load(['grade', 'classTeacher']);

        $timetable = TimetableEntry::getClassTimetable($classSection->id, $academicYear->id);
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.class-timetable', [
            'classSection' => $classSection,
            'academicYear' => $academicYear,
            'timetable' => $timetable,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $filename = "timetable-{$classSection->grade->name}-{$classSection->name}-{$academicYear->name}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Print/Stream teacher schedule as PDF
     */
    public function printTeacherSchedule(Request $request, Teacher $teacher, AcademicYear $academicYear)
    {
        $timetable = TimetableEntry::getTeacherTimetable($teacher->id, $academicYear->id);
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $loadSummary = TimetableEntry::getTeacherLoadSummary($teacher->id, $academicYear->id);

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.teacher-schedule', [
            'teacher' => $teacher,
            'academicYear' => $academicYear,
            'timetable' => $timetable,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'totalPeriods' => $loadSummary['totalPeriods'],
            'classesTaught' => $loadSummary['classesTaught'],
            'subjectsTaught' => $loadSummary['subjectsTaught'],
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $teacherName = str_replace(' ', '-', $teacher->name);
        $filename = "schedule-{$teacherName}-{$academicYear->name}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Download teacher schedule as PDF
     */
    public function downloadTeacherSchedule(Request $request, Teacher $teacher, AcademicYear $academicYear)
    {
        $timetable = TimetableEntry::getTeacherTimetable($teacher->id, $academicYear->id);
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        $loadSummary = TimetableEntry::getTeacherLoadSummary($teacher->id, $academicYear->id);

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.teacher-schedule', [
            'teacher' => $teacher,
            'academicYear' => $academicYear,
            'timetable' => $timetable,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'totalPeriods' => $loadSummary['totalPeriods'],
            'classesTaught' => $loadSummary['classesTaught'],
            'subjectsTaught' => $loadSummary['subjectsTaught'],
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $teacherName = str_replace(' ', '-', $teacher->name);
        $filename = "schedule-{$teacherName}-{$academicYear->name}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Print/Stream master timetable (all classes) as PDF
     */
    public function printMasterTimetable(Request $request, AcademicYear $academicYear)
    {
        // Get all active class sections for this academic year
        $classSections = ClassSection::with('grade')
            ->where('is_active', true)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->sortBy(fn($cs) => [$cs->grade->level ?? 0, $cs->name]);

        // Get all periods
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get all timetable entries and organize them
        $entries = TimetableEntry::with(['subject', 'teacher', 'classSection.grade'])
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->get();

        // Organize entries by class_section_id -> period_id -> day
        $timetableData = [];
        foreach ($entries as $entry) {
            $timetableData[$entry->class_section_id][$entry->timetable_period_id][$entry->day_of_week] = $entry;
        }

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.master-timetable', [
            'academicYear' => $academicYear,
            'classSections' => $classSections,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'timetableData' => $timetableData,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $filename = "master-timetable-{$academicYear->name}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Download master timetable (all classes) as PDF
     */
    public function downloadMasterTimetable(Request $request, AcademicYear $academicYear)
    {
        // Get all active class sections for this academic year
        $classSections = ClassSection::with('grade')
            ->where('is_active', true)
            ->where('academic_year_id', $academicYear->id)
            ->get()
            ->sortBy(fn($cs) => [$cs->grade->level ?? 0, $cs->name]);

        // Get all periods
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // Get all timetable entries and organize them
        $entries = TimetableEntry::with(['subject', 'teacher', 'classSection.grade'])
            ->where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->get();

        // Organize entries by class_section_id -> period_id -> day
        $timetableData = [];
        foreach ($entries as $entry) {
            $timetableData[$entry->class_section_id][$entry->timetable_period_id][$entry->day_of_week] = $entry;
        }

        $schoolSettings = SchoolSettings::first();

        $pdf = Pdf::loadView('pdf.master-timetable', [
            'academicYear' => $academicYear,
            'classSections' => $classSections,
            'periods' => $periods,
            'days' => TimetableEntry::DAYS,
            'timetableData' => $timetableData,
            'schoolSettings' => $schoolSettings,
            'schoolName' => $schoolSettings->school_name ?? config('app.name'),
            'schoolAddress' => $schoolSettings->address ?? '',
            'schoolPhone' => $schoolSettings->phone ?? '',
            'schoolLogo' => $schoolSettings->school_logo ? public_path('storage/' . $schoolSettings->school_logo) : null,
            'generatedAt' => now()->format('F d, Y h:i A'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isHtml5ParserEnabled', true);

        $filename = "master-timetable-{$academicYear->name}.pdf";

        return $pdf->download($filename);
    }
}
