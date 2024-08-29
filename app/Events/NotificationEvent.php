<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $currentUserId;
    public $notifyToUserId;
    public $message;
    public $notifyUserType;
    public $otherUserType;
    public $data;
    public $title;
    public $notificationType;

    /**
     * Create a new event instance.
     */
    public function __construct($currentUserId, $notifyToUserId, $message, $notifyUserType = '', $otherUserType = '', $data = [], $title = '', $notificationType = '')
    {
        $this->currentUserId = $currentUserId;
        $this->notifyToUserId = $notifyToUserId;
        $this->message = $message;
        $this->notifyUserType = $notifyUserType;
        $this->otherUserType = $otherUserType;
        $this->data = $data;
        $this->title = $title;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
