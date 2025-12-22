<?php

namespace App\Notifications;

use App\Models\HomeworkSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class HomeworkSubmitted extends Notification
{
    use Queueable;

    public function __construct(
        public HomeworkSubmission $submission
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->submission->student?->name ?? 'Unknown Student';
        $homeworkTitle = $this->submission->homework?->title ?? 'Unknown Homework';

        return [
            'title' => 'Homework Submitted',
            'message' => "{$studentName} submitted homework: {$homeworkTitle}",
            'submission_id' => $this->submission->id,
            'homework_id' => $this->submission->homework_id,
            'student_name' => $studentName,
            'homework_title' => $homeworkTitle,
            'icon' => 'heroicon-o-document-check',
            'color' => 'info',
            'url' => "/admin/homework/{$this->submission->homework_id}",
        ];
    }
}
