<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleDisplayed implements ShouldBroadcast
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
        return 'schedule.displayed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'tradie_id' => $this->scheduleData['tradie_id'] ?? null,
            'job_id' => $this->scheduleData['job_id'] ?? null,
            'action' => $this->scheduleData['action'] ?? 'displayed',
            'message' => $this->scheduleData['message'] ?? 'Schedule updated',
            'timestamp' => now()->toISOString(),
        ];
    }
}
