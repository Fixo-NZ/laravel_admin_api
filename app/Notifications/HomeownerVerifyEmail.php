<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class HomeownerVerifyEmail extends VerifyEmail
{
    /**
     * Get the verification URL for the notification.
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'homeowner.verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
