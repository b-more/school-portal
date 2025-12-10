<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HomeworkNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Homework Assignment - ' . ($this->data['subject_name'] ?? 'Homework'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.homework-notification',
            with: [
                'parent_name' => $this->data['parent_name'],
                'student_name' => $this->data['student_name'],
                'subject_name' => $this->data['subject_name'],
                'homework_title' => $this->data['homework_title'],
                'homework_description' => $this->data['homework_description'],
                'grade_name' => $this->data['grade_name'],
                'teacher_name' => $this->data['teacher_name'],
                'due_date' => $this->data['due_date'],
                'max_score' => $this->data['max_score'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
