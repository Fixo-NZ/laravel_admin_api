<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS')); // <-- absolute path
        $this->messaging = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createMessaging();
    }

    public function send($token, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }

    public function sendToTopic($topic, $title, $body, $data = [])
    {
        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification(Notification::create($title, $body))
            ->withData($data);

        return $this->messaging->send($message);
    }
}
