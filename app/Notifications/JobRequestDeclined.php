<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class JobRequestDeclined extends Notification
{
    use Queueable;

    private $job;
    private $tradie;

    

    public function __construct($job, $tradie)
    {
        $this->job = $job;
        $this->tradie = $tradie;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Job Request Declined',
            'message' => "{$this->tradie->full_name} declined your job request",
            'job_title' => $this->job->title,
            'job_type' => $this->job->category,
            'timestamp' => now(),
        ];
    }
}
