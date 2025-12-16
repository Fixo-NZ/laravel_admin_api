<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(public $booking) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'job_cancelled',
            'title' => 'Booking Cancelled',
            'message' => 'A homeowner cancelled the booking.',
            'booking_id' => $this->booking->id,
        ];
    }
}
