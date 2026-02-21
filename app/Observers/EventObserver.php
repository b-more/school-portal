<?php

namespace App\Observers;

use App\Models\Event;
use App\Notifications\NewEventCreated;
use App\Services\AdminNotificationService;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        AdminNotificationService::notifyAdmins(new NewEventCreated($event));
    }
}
