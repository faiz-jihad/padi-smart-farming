<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification)
    {
        $this->notification->loadMissing('user');
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new Channel('notifications.broadcast'),
        ];

        if ($this->notification->user_id) {
            $channels[] = new Channel('notifications.user.'.$this->notification->user_id);
            $channels[] = new PrivateChannel('admin.notifications.'.$this->notification->user_id);
            $channels[] = new PrivateChannel('notifications.'.$this->notification->user_id);
        }

        $roleTarget = $this->notification->data['role_target'] ?? null;
        if ($roleTarget) {
            $channels[] = new Channel('notifications.role.'.$roleTarget);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'admin.notification.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $this->notification->body,
            'data' => $this->notification->data ?? [],
            'read_at' => $this->notification->read_at?->toISOString(),
            'created_at' => $this->notification->created_at?->toIso8601String(),
            'created_at_human' => $this->notification->created_at?->diffForHumans(),
        ];
    }
}
