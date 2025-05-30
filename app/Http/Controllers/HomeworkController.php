<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentGuardian;
use App\Constants\RoleConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HomeworkController extends Controller
{
    /**
     * Download homework file
     */
    public function download(Homework $homework)
    {
        // Check if user has permission to download this homework
        if (!$this->canAccessHomework($homework)) {
            abort(403, 'You do not have permission to access this homework.');
        }

        // Check if file exists
        if (!$homework->homework_file) {
            abort(404, 'Homework file not found.');
        }

        $filePath = storage_path('app/public/' . $homework->homework_file);

        if (!file_exists($filePath)) {
            abort(404, 'Homework file not found on server.');
        }

        // Generate download filename
        $fileName = $this->generateDownloadFileName($homework);

        // Log the download
        $this->logDownload($homework);

        // Return file download response
        return Response::download($filePath, $fileName);
    }

    /**
     * View homework file in browser
     */
    public function view(Homework $homework)
    {
        // Check if user has permission to view this homework
        if (!$this->canAccessHomework($homework)) {
            abort(403, 'You do not have permission to access this homework.');
        }

        // Check if file exists
        if (!$homework->homework_file) {
            abort(404, 'Homework file not found.');
        }

        $filePath = storage_path('app/public/' . $homework->homework_file);

        if (!file_exists($filePath)) {
            abort(404, 'Homework file not found on server.');
        }

        // Get file mime type
        $mimeType = mime_content_type($filePath);

        // Log the view
        $this->logView($homework);

        // Return file response for viewing in browser
        return Response::file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $this->generateDownloadFileName($homework) . '"'
        ]);
    }

    /**
     * View homework details (API endpoint for parent/student portal)
     */
    public function show(Homework $homework)
    {
        $user = Auth::user();

        // Check if user has permission to view this homework
        if (!$this->canAccessHomework($homework)) {
            abort(403, 'You do not have permission to access this homework.');
        }

        return response()->json([
            'homework' => [
                'id' => $homework->id,
                'title' => $homework->title,
                'description' => $homework->description,
                'subject' => $homework->subject->name,
                'grade' => $homework->grade->name,
                'teacher' => $homework->assignedBy->name,
                'due_date' => $homework->due_date->format('Y-m-d H:i:s'),
                'due_date_formatted' => $homework->due_date->format('M j, Y g:i A'),
                'max_score' => $homework->max_score,
                'status' => $homework->status,
                'has_file' => !empty($homework->homework_file),
                'file_url' => $homework->homework_file ? route('homework.view', $homework) : null,
                'download_url' => $homework->homework_file ? route('homework.download', $homework) : null,
                'created_at' => $homework->created_at->format('Y-m-d H:i:s'),
                'is_overdue' => $homework->due_date->isPast(),
                'days_until_due' => $homework->due_date->diffInDays(now(), false),
                'allow_late_submission' => $homework->allow_late_submission ?? false,
                'late_submission_deadline' => $homework->late_submission_deadline ? $homework->late_submission_deadline->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }

    /**
     * Download homework file (alternative route)
     */
    public function downloadHomeworkFile(Homework $homework)
    {
        return $this->download($homework);
    }

    /**
     * Download homework resources (if any additional files)
     */
    public function downloadResources(Homework $homework)
    {
        // This can be extended for multiple resource files
        return $this->download($homework);
    }

    /**
     * Download all submissions for a homework (for teachers)
     */
    public function downloadAllSubmissions(Homework $homework)
    {
        $user = Auth::user();

        // Only teachers and admins can download all submissions
        if (!$user || !in_array($user->role_id, [RoleConstants::ADMIN, RoleConstants::TEACHER])) {
            abort(403, 'Access denied');
        }

        // Check if teacher has permission for this homework
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher || $homework->assigned_by !== $teacher->id) {
                abort(403, 'You can only download submissions for homework you assigned');
            }
        }

        // This would create a ZIP file of all submissions
        // For now, return a message that this feature is under development
        return response()->json([
            'message' => 'Bulk submission download feature is under development',
            'homework_id' => $homework->id,
            'homework_title' => $homework->title
        ]);
    }

    /**
     * Download homework submission file
     */
    public function downloadSubmission($submission)
    {
        // This would handle homework submission downloads
        // Implementation depends on your HomeworkSubmission model structure
        return response()->json([
            'message' => 'Submission download feature is under development',
            'submission_id' => $submission
        ]);
    }

    /**
     * Check if the current user can access the homework
     */
    protected function canAccessHomework(Homework $homework): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Admin can access all homework
        if ($user->role_id === RoleConstants::ADMIN) {
            return true;
        }

        // Teacher can access homework they assigned or for grades they teach
        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                return false;
            }

            // Check if teacher assigned this homework
            if ($homework->assigned_by === $teacher->id) {
                return true;
            }

            // Check if teacher teaches this grade
            return $teacher->classSections()
                ->whereHas('grade', function ($query) use ($homework) {
                    $query->where('id', $homework->grade_id);
                })
                ->exists();
        }

        // Parent can access homework for their children's grades
        if ($user->role_id === RoleConstants::PARENT) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

            if (!$parentGuardian) {
                return false;
            }

            // Check if parent has children in this grade
            return $parentGuardian->students()
                ->where('enrollment_status', 'active')
                ->where('grade_id', $homework->grade_id)
                ->exists();
        }

        // Student can access homework for their grade
        if ($user->role_id === RoleConstants::STUDENT) {
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return false;
            }

            return $student->grade_id === $homework->grade_id &&
                   $student->enrollment_status === 'active';
        }

        return false;
    }

    /**
     * Generate a proper filename for download
     */
    protected function generateDownloadFileName(Homework $homework): string
    {
        // Clean up title for filename
        $title = preg_replace('/[^A-Za-z0-9\-_]/', '_', $homework->title);
        $title = preg_replace('/_+/', '_', $title);
        $title = trim($title, '_');

        // Get subject and grade names
        $subject = preg_replace('/[^A-Za-z0-9\-_]/', '_', $homework->subject->name);
        $grade = preg_replace('/[^A-Za-z0-9\-_]/', '_', $homework->grade->name);

        // Get file extension
        $extension = pathinfo($homework->homework_file, PATHINFO_EXTENSION);

        // Construct filename
        $filename = "{$grade}_{$subject}_{$title}";

        // Limit filename length
        if (strlen($filename) > 100) {
            $filename = substr($filename, 0, 100);
        }

        return $filename . '.' . $extension;
    }

    /**
     * Log homework download
     */
    protected function logDownload(Homework $homework): void
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('Homework file downloaded', [
            'homework_id' => $homework->id,
            'homework_title' => $homework->title,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role_id,
            'file_name' => $homework->homework_file,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'downloaded_at' => now(),
        ]);
    }

    /**
     * Log homework view
     */
    protected function logView(Homework $homework): void
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\Log::info('Homework file viewed', [
            'homework_id' => $homework->id,
            'homework_title' => $homework->title,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role_id,
            'file_name' => $homework->homework_file,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now(),
        ]);
    }

    /**
     * Get homework list for a specific grade (API endpoint)
     */
    public function getHomeworkByGrade($gradeId)
    {
        // Check if user has permission to view homework for this grade
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $homework = Homework::where('grade_id', $gradeId)
            ->where('status', 'active')
            ->with(['subject', 'grade', 'assignedBy'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Filter homework based on user permissions
        $filteredHomework = $homework->filter(function ($hw) {
            return $this->canAccessHomework($hw);
        });

        return response()->json([
            'homework' => $filteredHomework->map(function ($hw) {
                return [
                    'id' => $hw->id,
                    'title' => $hw->title,
                    'description' => $hw->description,
                    'subject' => $hw->subject->name,
                    'grade' => $hw->grade->name,
                    'teacher' => $hw->assignedBy->name,
                    'due_date' => $hw->due_date->format('Y-m-d H:i:s'),
                    'due_date_formatted' => $hw->due_date->format('M j, Y g:i A'),
                    'max_score' => $hw->max_score,
                    'status' => $hw->status,
                    'has_file' => !empty($hw->homework_file),
                    'file_url' => $hw->homework_file ? route('homework.view', $hw) : null,
                    'download_url' => $hw->homework_file ? route('homework.download', $hw) : null,
                    'is_overdue' => $hw->due_date->isPast(),
                    'days_until_due' => $hw->due_date->diffInDays(now(), false),
                ];
            })->values()
        ]);
    }

    /**
     * Get homework statistics
     */
    public function getHomeworkStats()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        $stats = [];

        if ($user->role_id === RoleConstants::TEACHER) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if ($teacher) {
                $stats = [
                    'total_assigned' => Homework::where('assigned_by', $teacher->id)->count(),
                    'active' => Homework::where('assigned_by', $teacher->id)->where('status', 'active')->count(),
                    'completed' => Homework::where('assigned_by', $teacher->id)->where('status', 'completed')->count(),
                    'overdue' => Homework::where('assigned_by', $teacher->id)
                        ->where('due_date', '<', now())
                        ->where('status', 'active')
                        ->count(),
                ];
            }
        } elseif ($user->role_id === RoleConstants::PARENT) {
            $parentGuardian = ParentGuardian::where('user_id', $user->id)->first();

            if ($parentGuardian) {
                $childrenGradeIds = $parentGuardian->students()
                    ->where('enrollment_status', 'active')
                    ->pluck('grade_id')
                    ->unique();

                $stats = [
                    'total_available' => Homework::whereIn('grade_id', $childrenGradeIds)->count(),
                    'active' => Homework::whereIn('grade_id', $childrenGradeIds)->where('status', 'active')->count(),
                    'overdue' => Homework::whereIn('grade_id', $childrenGradeIds)
                        ->where('due_date', '<', now())
                        ->where('status', 'active')
                        ->count(),
                    'due_soon' => Homework::whereIn('grade_id', $childrenGradeIds)
                        ->where('due_date', '>=', now())
                        ->where('due_date', '<=', now()->addDays(3))
                        ->where('status', 'active')
                        ->count(),
                ];
            }
        }

        return response()->json(['stats' => $stats]);
    }
}
