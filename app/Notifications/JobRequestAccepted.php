<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobRequestAccepted extends Notification
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
            'type' => 'job_accepted',
            'title' => 'Job Request Accepted!',
            'message' => $this->booking->tradie->first_name .
                ' accepted your job request.',
            'booking_id' => $this->booking->id,
            'tradie_id' => $this->booking->tradie_id,
        ];
    }
}
