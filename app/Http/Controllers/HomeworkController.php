<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class HomeworkController extends Controller
{
    /**
     * Display the homework file for download
     */
    public function downloadHomeworkFile(Homework $homework)
    {
        if (empty($homework->homework_file)) {
            return back()->with('error', 'No homework file available for download.');
        }

        $path = $homework->homework_file;

        if (!Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Homework file not found in storage.');
        }

        $fileName = basename($path);
        $displayName = $homework->title . ' - ' . $fileName;

        return Storage::disk('public')->download($path, $displayName);
    }

    /**
     * Download additional resources files for homework
     */
    public function downloadResources(Homework $homework)
    {
        if (empty($homework->file_attachment)) {
            return back()->with('error', 'No resources available for download.');
        }

        // If there's only one file, download it directly
        if (count($homework->file_attachment) === 1) {
            $path = $homework->file_attachment[0];
            $fileName = basename($path);
            $displayName = $homework->title . ' - Resource - ' . $fileName;

            return Storage::disk('public')->download($path, $displayName);
        }

        // Create a zip file for multiple files
        $zipFileName = Str::slug($homework->title) . '-resources.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($homework->file_attachment as $file) {
                $filePath = Storage::disk('public')->path($file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($file));
                }
            }

            $zip->close();

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Could not create zip file for download.');
    }

    /**
     * Download submission files
     */
    public function downloadSubmission(HomeworkSubmission $submission)
    {
        if (empty($submission->file_attachment)) {
            return back()->with('error', 'No submission files available for download.');
        }

        // If there's only one file, download it directly
        if (count($submission->file_attachment) === 1) {
            $path = $submission->file_attachment[0];
            $fileName = basename($path);
            $student = $submission->student;
            $displayName = ($student ? $student->name . ' - ' : '') . $fileName;

            return Storage::disk('public')->download($path, $displayName);
        }

        // Create a zip file for multiple files
        $student = $submission->student;
        $studentName = $student ? Str::slug($student->name) : 'student';
        $zipFileName = $studentName . '-submission.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($submission->file_attachment as $file) {
                $filePath = Storage::disk('public')->path($file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($file));
                }
            }

            $zip->close();

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Could not create zip file for download.');
    }

    /**
     * Download all submissions for a homework assignment as a zip file
     */
    public function downloadAllSubmissions(Homework $homework)
    {
        $submissions = $homework->submissions()->with('student')->get();

        if ($submissions->isEmpty()) {
            return back()->with('error', 'No submissions available for download.');
        }

        // Create a zip file name
        $zipFileName = Str::slug($homework->title) . '-all-submissions.zip';
        $zipPath = storage_path('app/public/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!Storage::disk('public')->exists('temp')) {
            Storage::disk('public')->makeDirectory('temp');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($submissions as $submission) {
                if (empty($submission->file_attachment)) {
                    continue;
                }

                $student = $submission->student;
                $studentName = $student ? Str::slug($student->name) : 'unknown-student';

                // Create a directory for each student
                $dirName = $studentName . '/';

                foreach ($submission->file_attachment as $file) {
                    $filePath = Storage::disk('public')->path($file);
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, $dirName . basename($file));
                    }
                }
            }

            $zip->close();

            return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Could not create zip file for download.');
    }
}
