<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisasterEarlyWarningAlert implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param array<string, mixed> $alertData
     */
    public function __construct(
        public array $alertData
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('disaster-alerts');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'disaster.alert';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'alert' => $this->alertData,
            'timestamp' => now()->toISOString(),
        ];
    }
}
