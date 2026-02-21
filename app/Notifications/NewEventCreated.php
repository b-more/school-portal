<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEventCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Event $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $eventDate = $this->event->start_date?->format('M d, Y') ?? 'TBD';

        return [
            'title' => 'New Event Scheduled',
            'message' => "'{$this->event->title}' scheduled for {$eventDate}.",
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_date' => $eventDate,
            'icon' => 'heroicon-o-calendar-days',
            'color' => 'info',
            'url' => "/admin/events/{$this->event->id}",
        ];
    }
}
