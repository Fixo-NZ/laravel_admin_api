<?php

namespace App\Services;

use App\Contracts\EmailServiceInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;
use Exception;

class EmailService implements EmailServiceInterface
{
    /**
     * Send an email immediately
     * 
     * @param string|array $to
     * @param Mailable $mailable
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function send($to, Mailable $mailable): array
    {
        try {
            Mail::to($to)->send($mailable);

            $this->logSuccess('sent', $to, $mailable);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('send', $to, $mailable, $e);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Queue an email for asynchronous sending
     * 
     * @param string|array $to
     * @param Mailable $mailable
     * @param string|null $queue - Optional queue name
     * @return array
     */
    public function queue($to, Mailable $mailable, ?string $queue = null): array
    {
        try {
            if ($queue) {
                Mail::to($to)->queue($mailable->onQueue($queue));
            } else {
                Mail::to($to)->queue($mailable);
            }

            $this->logSuccess('queued', $to, $mailable, ['queue' => $queue ?? 'default']);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('queue', $to, $mailable, $e);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send email with CC recipients
     * 
     * @param string|array $to
     * @param string|array $cc
     * @param Mailable $mailable
     * @return array
     */
    public function sendWithCc($to, $cc, Mailable $mailable): array
    {
        try {
            Mail::to($to)->cc($cc)->send($mailable);

            $this->logSuccess('sent with CC', $to, $mailable, ['cc' => $cc]);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('send with CC', $to, $mailable, $e, ['cc' => $cc]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send email with BCC recipients
     * 
     * @param string|array $to
     * @param string|array $bcc
     * @param Mailable $mailable
     * @return array
     */
    public function sendWithBcc($to, $bcc, Mailable $mailable): array
    {
        try {
            Mail::to($to)->bcc($bcc)->send($mailable);

            $this->logSuccess('sent with BCC', $to, $mailable);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('send with BCC', $to, $mailable, $e);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send email with both CC and BCC
     * 
     * @param string|array $to
     * @param string|array $cc
     * @param string|array $bcc
     * @param Mailable $mailable
     * @return array
     */
    public function sendWithCcAndBcc($to, $cc, $bcc, Mailable $mailable): array
    {
        try {
            Mail::to($to)->cc($cc)->bcc($bcc)->send($mailable);

            $this->logSuccess('sent with CC and BCC', $to, $mailable);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('send with CC and BCC', $to, $mailable, $e);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Queue email with delay
     * 
     * @param string|array $to
     * @param Mailable $mailable
     * @param \DateTime|\DateInterval|int $delay
     * @return array
     */
    public function queueWithDelay($to, Mailable $mailable, $delay): array
    {
        try {
            Mail::to($to)->later($delay, $mailable);

            $this->logSuccess('queued with delay', $to, $mailable, ['delay' => $delay]);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->logError('queue with delay', $to, $mailable, $e);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send to multiple recipients
     * 
     * @param array $recipients
     * @param Mailable $mailable
     * @param bool $queue
     * @return array ['sent' => int, 'failed' => int, 'errors' => array]
     */
    public function sendBulk(array $recipients, Mailable $mailable, bool $queue = true): array
    {
        $sent = 0;
        $failed = 0;
        $errors = [];

        foreach ($recipients as $recipient) {
            $result = $queue
                ? $this->queue($recipient, clone $mailable)
                : $this->send($recipient, clone $mailable);

            if ($result['success']) {
                $sent++;
            } else {
                $failed++;
                $errors[$recipient] = $result['error'];
            }
        }

        Log::info('Bulk email operation completed', [
            'total' => count($recipients),
            'sent' => $sent,
            'failed' => $failed,
            'queued' => $queue
        ]);

        return [
            'sent' => $sent,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Test email configuration
     * 
     * @param string $to
     * @return array
     */
    public function sendTestEmail(string $to): array
    {
        try {
            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($to) {
                $message->to($to)
                    ->subject('Test Email - ' . config('app.name'));
            });

            Log::info('Test email sent successfully', ['to' => $to]);

            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            Log::error('Test email failed', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Log successful email operation
     * 
     * @param string $action
     * @param mixed $to
     * @param Mailable $mailable
     * @param array $extra
     */
    protected function logSuccess(string $action, $to, Mailable $mailable, array $extra = []): void
    {
        Log::info("Email {$action} successfully", array_merge([
            'to' => $to,
            'mailable' => get_class($mailable),
            'timestamp' => now()->toDateTimeString()
        ], $extra));
    }

    /**
     * Log failed email operation
     * 
     * @param string $action
     * @param mixed $to
     * @param Mailable $mailable
     * @param Exception $exception
     * @param array $extra
     */
    protected function logError(string $action, $to, Mailable $mailable, Exception $exception, array $extra = []): void
    {
        Log::error("Email {$action} failed", array_merge([
            'to' => $to,
            'mailable' => get_class($mailable),
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'timestamp' => now()->toDateTimeString()
        ], $extra));
    }
}