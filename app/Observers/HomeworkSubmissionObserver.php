<?php

namespace App\Observers;

use App\Models\HomeworkSubmission;
use App\Notifications\HomeworkSubmitted;
use App\Services\AdminNotificationService;

class HomeworkSubmissionObserver
{
    /**
     * Handle the HomeworkSubmission "created" event.
     */
    public function created(HomeworkSubmission $submission): void
    {
        // Notify admins when homework is submitted
        if ($submission->is_submitted) {
            AdminNotificationService::notifyAdmins(new HomeworkSubmitted($submission));
        }
    }

    /**
     * Handle the HomeworkSubmission "updated" event.
     */
    public function updated(HomeworkSubmission $submission): void
    {
        // Notify when a submission is marked as submitted
        if ($submission->wasChanged('is_submitted') && $submission->is_submitted) {
            AdminNotificationService::notifyAdmins(new HomeworkSubmitted($submission));
        }
    }
}
