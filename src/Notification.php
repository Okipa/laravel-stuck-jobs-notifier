<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification as IlluminateNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NotificationChannels\Webhook\WebhookMessage;

class Notification extends IlluminateNotification
{
    protected $stuckFailedJobsCount;

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
            ->subject('[' . config('app.name') . ' - ' . config('app.env') . '] ⚠ ' . $this->stuckFailedJobsCount
                . ' stuck failed ' . Str::plural('job', $this->stuckFailedJobsCount) . ' detected')
            ->line($this->stuckFailedJobsCount . ' failed ' . Str::plural('job', $this->stuckFailedJobsCount)
                . ', stuck for at least ' . config('failed-jobs-notifier.daysLimit') . ' days, detected at '
                . config('app.url') . '.')
            ->line('Please check your failed jobs using the « php artisan queue:failed » command.');
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
            ->content('⚠ `' . config('app.name') . ' - ' . config('app.env') . '` ' . $this->stuckFailedJobsCount
                . ' failed ' . Str::plural('job', $this->stuckFailedJobsCount) . ', stuck for at least '
                . config('failed-jobs-notifier.daysLimit') . ' days, detected at ' . config('app.url') . '.');
    }

    /**
     * Get the webhook representation of the notification.
     *
     * @return \NotificationChannels\Webhook\WebhookMessage
     */
    public function toWebhook(): WebhookMessage
    {
        // rocket chat webhook example
        return WebhookMessage::create()->data([
            'text' => '⚠ `' . config('app.name') . ' - ' . config('app.env') . '` ' . $this->stuckFailedJobsCount
                . ' failed ' . Str::plural('job', $this->stuckFailedJobsCount) . ', stuck for at least '
                . config('failed-jobs-notifier.daysLimit') . ' days, detected at ' . config('app.url') . '.',
        ])->header('Content-Type', 'application/json');
    }
}
