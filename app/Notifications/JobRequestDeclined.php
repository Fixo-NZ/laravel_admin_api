<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobRequestDeclined extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'job_declined',
            'title' => 'Job Request Declined',
            'message' => $this->booking->tradie->first_name .
                ' declined your job request.',
            'booking_id' => $this->booking->id,
            'tradie_id' => $this->booking->tradie_id,
        ];
    }
}
