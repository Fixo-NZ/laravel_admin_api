<?php

namespace App\Notifications;

use App\Models\ReviewResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewResponseNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ReviewResponse $response)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New response to your review')
                    ->greeting('Hello '.$notifiable->first_name)
                    ->line('The tradie has responded to your review.')
                    ->line('Response: "'.$this->response->content.'"')
                    ->action('View Review', url('/')) // Replace with actual frontend URL
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'review_response',
            'review_id' => $this->response->review_id,
            'tradie_id' => $this->response->tradie_id,
            'content' => $this->response->content,
            'edited_at' => optional($this->response->edited_at)->toIso8601String(),
        ];
    }
}
