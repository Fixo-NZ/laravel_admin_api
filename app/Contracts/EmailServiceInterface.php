<?php

namespace App\Contracts;

use Illuminate\Mail\Mailable;

interface EmailServiceInterface
{
    public function send($to, Mailable $mailable): array;
    public function queue($to, Mailable $mailable, ?string $queue = null): array;
    public function sendWithCc($to, $cc, Mailable $mailable): array;
    public function sendWithBcc($to, $bcc, Mailable $mailable): array;
    public function queueWithDelay($to, Mailable $mailable, $delay): array;
    public function sendBulk(array $recipients, Mailable $mailable, bool $queue = true): array;
}