<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WeatherSoilUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param int $farmId
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public int $farmId,
        public array $payload
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('agri-telemetry');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'telemetry.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'farm_id' => $this->farmId,
            'payload' => $this->payload,
            'timestamp' => now()->toISOString(),
        ];
    }
}
