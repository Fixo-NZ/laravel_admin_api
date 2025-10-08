<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Schedule;

class ScheduleNotification extends Notification
{
    use Queueable;

    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

    public function via($notifiable): array
    {
        return ['mail']; // Send as email
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Upcoming Schedule: ' . $this->schedule->title)
            ->greeting('Hello!')
            ->line('You have a new schedule:')
            ->line('📌 ' . $this->schedule->title)
            ->line('📝 ' . ($this->schedule->description ?? 'No description'))
            ->line('🕒 From: ' . $this->schedule->start_time)
            ->line('🕒 To: ' . $this->schedule->end_time)
            ->action('View Schedule', url('/schedules/' . $this->schedule->id))
            ->line('Thank you for using our application!');
    }
}
