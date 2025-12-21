<?php

namespace App\Notifications;

use App\Models\Homeowner;
use App\Models\Tradie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtp extends Notification
{
    use Queueable;
    protected $otp;
    /**
     * Create a new notification instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
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
        $user = Tradie::where('phone', $this->otp->phone)->first();
        if (!$user) {
            $user = Homeowner::where('phone', $this->otp->phone)->first();
        }

        $fullName = trim(
            $user->first_name . ' ' .
            $user->last_name
        );

        return (new MailMessage)
            ->subject('Your FIXO OTP Code')
            ->line('This is your One-Time Password (OTP) for ' . $fullName . '. Please use this code to complete your verification process.')
            ->line('**' . $this->otp->otp_code . '**')
            ->line('For your security, never share this code with anyone.');

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
