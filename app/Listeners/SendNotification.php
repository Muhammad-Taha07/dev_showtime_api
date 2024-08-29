<?php

namespace App\Listeners;

use App\Models\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event)
    {
        return Notification::create([
            'notify_user_id' => $event->currentUserId,
            'notify_user_type' => $event->notifyUserType ?? 'agent',
            'other_user_id' => $event->notifyToUserId,
            'other_user_type' => $event->otherUserType ?? 'agent',
            'title' => $event->title ?? 'Title',
            'message' => $event->message,
            'notification_type' => $event->notificationType ?? 'push',
            'data' => json_encode($event->data)
        ]);
    }
}
