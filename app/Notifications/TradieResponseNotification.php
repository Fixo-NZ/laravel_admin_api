<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TradieResponseNotification extends Notification
{
    use Queueable;

    public $booking;
    public $status; // 'accepted' or 'declined'

    public function __construct($booking, $status)
    {
        $this->booking = $booking;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'tradie_response',
            'booking_id' => $this->booking->id,
            'tradie_id' => $this->booking->tradie_id,
            'tradie_name' => $this->booking->tradie->first_name . ' ' . $this->booking->tradie->last_name,
            'status' => $this->status,
            'message' => $this->status === 'accepted'
                ? 'Your booking has been accepted by the tradie.'
                : 'Your booking has been declined by the tradie.',
        ];
    }
}
