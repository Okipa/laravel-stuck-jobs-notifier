<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification as IlluminateNotification;
use Illuminate\Support\Collection;
use NotificationChannels\Webhook\WebhookMessage;

class Notification extends IlluminateNotification
{
    protected $stuckFailedJobsCount;

    /**
     * Create a new notification instance.
     *
     * @param \Illuminate\Support\Collection $stuckFailedJobs
     */
    public function __construct(Collection $stuckFailedJobs)
    {
        $this->stuckFailedJobsCount = $stuckFailedJobs->count();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return config('failed-jobs-notifier.channels');
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)->error()
            ->subject($this->stuckFailedJobsCount . ' failed jobs are stuck at ' . config('app.url'))
            ->line($this->stuckFailedJobsCount . ' failed jobs are stuck for '
                . config('failed-jobs-notifier.daysLimit') . 'days at ' . config('app.url') . '.')
            ->line('Please check your failed jobs on your project server with the '
                . '« php artisan queue:failed » command.');
    }

    /**
     * Get the slack representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->error()
            ->content($this->stuckFailedJobsCount . ' failed jobs are stuck at ' . config('app.url') . '.');
    }

    /**
     * Get the webhook representation of the notification.
     *
     * @return \NotificationChannels\Webhook\WebhookMessage
     */
    public function toWebhook(): WebhookMessage
    {
        // this is a rocket chat example
        return WebhookMessage::create()->data([
            'payload' => [
                'text'        => $this->stuckFailedJobsCount . ' failed jobs are stuck at ' . config('app.url') . '.',
                'attachments' => [
                    'title'      => 'Rocket.Chat',
                    'title_link' => 'https://rocket.chat',
                    'text'       => 'Rocket.Chat, the best open source chat',
                    'image_url'  => '/images/integration-attachment-example.png',
                    'color'      => '#764FA5',
                ],
            ],
        ])
            // ->userAgent("Custom-User-Agent")
            ->header('Content-Type', 'application/json');
    }
}
