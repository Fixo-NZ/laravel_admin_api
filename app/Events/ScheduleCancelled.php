<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $scheduleData;

    /**
     * Create a new event instance.
     */
    public function __construct($scheduleData = [])
    {
        $this->scheduleData = $scheduleData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('schedule-updates'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'schedule.cancelled';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'action' => 'cancelled',
            'schedule' => $this->scheduleData['schedule'] ?? null,
            'tradie_id' => $this->scheduleData['tradie_id'] ?? null,
            'homeowner_id' => $this->scheduleData['homeowner_id'] ?? null,
            'timestamp' => now()->toISOString(),
        ];
    }
}
