<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobRequestAccepted extends Notification
{
    use Queueable;

    private $job;
    private $homeowner;

    public function __construct($job, $homeowner)
    {
        $this->job = $job;
        $this->homeowner = $homeowner;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Job Request Accepted!',
            'message' => "{$this->homeowner->full_name} has accepted your job request",
            'job_title' => $this->job->title,
            'job_type' => $this->job->category,
            'timestamp' => now(),
        ];
    }
}
