<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HomeworkController extends Controller
{
    /**
     * Download homework file
     */
    public function download(Homework $homework)
    {
        $user = Auth::user();

        // Check if user has permission to download this homework
        if ($user->hasRole('parent')) {
            // For parents, check if any of their children are in the homework's grade
            $parent = $user->parentGuardian;
            if (!$parent) {
                abort(403, 'Access denied');
            }

            $studentInGrade = $parent->students()
                ->where('grade', $homework->grade)
                ->where('enrollment_status', 'active')
                ->exists();

            if (!$studentInGrade) {
                abort(403, 'None of your children are in this grade');
            }
        } elseif ($user->hasRole('student')) {
            // For students, check if they are in the homework's grade
            $student = $user->student;
            if (!$student || $student->grade !== $homework->grade) {
                abort(403, 'This homework is not for your grade');
            }
        } elseif (!$user->hasRole(['admin', 'teacher'])) {
            abort(403, 'Access denied');
        }

        // Check if homework has a file
        if (empty($homework->homework_file)) {
            abort(404, 'No file attached to this homework');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($homework->homework_file)) {
            abort(404, 'Homework file not found');
        }

        // Get file path and name
        $filePath = Storage::disk('public')->path($homework->homework_file);
        $fileName = basename($homework->homework_file);

        // Create a friendly filename
        $friendlyName = $homework->title . ' - ' . $homework->subject->name . ' - ' . $homework->grade;
        $friendlyName = preg_replace('/[^A-Za-z0-9\-_. ]/', '', $friendlyName);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $downloadName = $friendlyName . '.' . $extension;

        // Return file download response
        return response()->download($filePath, $downloadName, [
            'Content-Type' => Storage::disk('public')->mimeType($homework->homework_file),
        ]);
    }

    /**
     * View homework details (API endpoint for parent/student portal)
     */
    public function show(Homework $homework)
    {
        $user = Auth::user();
        $canDownload = false;

        // Check permissions
        if ($user->hasRole('parent')) {
            $parent = $user->parentGuardian;
            if ($parent) {
                $studentInGrade = $parent->students()
                    ->where('grade', $homework->grade)
                    ->where('enrollment_status', 'active')
                    ->exists();
                $canDownload = $studentInGrade;
            }
        } elseif ($user->hasRole('student')) {
            $student = $user->student;
            $canDownload = $student && $student->grade === $homework->grade;
        } elseif ($user->hasRole(['admin', 'teacher'])) {
            $canDownload = true;
        }

        return response()->json([
            'homework' => [
                'id' => $homework->id,
                'title' => $homework->title,
                'subject' => $homework->subject->name,
                'grade' => $homework->grade,
                'description' => $homework->description,
                'due_date' => $homework->due_date->format('Y-m-d'),
                'teacher' => $homework->assignedBy->name ?? 'Unknown',
                'status' => $homework->status,
                'has_file' => !empty($homework->homework_file),
                'can_download' => $canDownload,
                'download_url' => $canDownload ? route('homework.download', $homework) : null,
            ]
        ]);
    }
}
