<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobBookedNotification extends Notification
{
    use Queueable;

    public function __construct(private Booking $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'job_booked',
            'title' => 'New Job Request',
            'message' => 'A homeowner has booked you.',
            'booking_id' => $this->booking->id,
            'service_id' => $this->booking->service_id,
            'booking_start' => $this->booking->booking_start,
            'booking_end' => $this->booking->booking_end,
            'status' => $this->booking->status, // <- add this
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'job_booked',
            'title' => 'New Job Request',
            'message' => 'A homeowner has booked you.',
            'booking_id' => $this->booking->id,
            'service_id' => $this->booking->service_id,
            'booking_start' => $this->booking->start_at?->toIso8601String(),
            'booking_end' => $this->booking->end_at?->toIso8601String(),
            'status' => $this->booking->status, // <- add this
        ];
    }
}
