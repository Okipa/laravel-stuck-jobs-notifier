<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification as IlluminateNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NotificationChannels\Webhook\WebhookMessage;

class Notification extends IlluminateNotification
{
    protected int $stuckJobsCount;

    protected int $isPlural;

    public function __construct(Collection $stuckJobs)
    {
        $this->stuckJobsCount = $stuckJobs->count();
        $this->isPlural = $this->stuckJobsCount > 1;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return config('stuck-jobs-notifier.channels');
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage)->level('error')
            ->subject('⚠ ' . $this->stuckJobsCount . ' stuck ' . Str::plural('job', $this->stuckJobsCount)
                . ' detected')
            ->line('We have detected' . $this->stuckJobsCount . ' failed ' . Str::plural('job', $this->stuckJobsCount)
                . ' that ' . ($this->isPlural ? 'are' : 'is') . ' stuck since at least '
                . config('stuck-jobs-notifier.hours_limit') . ' hours on [' . config('app.name') . ']('
                . config('app.url') . ').')
            ->line('Please check your stuck jobs connecting to your server and using the '
                . '« php artisan queue:failed » command.');
    }

    /**
     * Get the slack representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)->error()->content('⚠ `' . config('app.name') . ' - ' . config('app.env') . '` '
            . $this->stuckJobsCount . ' failed ' . Str::plural('job', $this->stuckJobsCount) . ', stuck for at least '
            . config('stuck-jobs-notifier.hours_limit') . ' hours, detected at ' . config('app.url') . '.');
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
            'text' => '⚠ `' . config('app.name') . ' - ' . config('app.env') . '` ' . $this->stuckJobsCount
                . ' failed ' . Str::plural('job', $this->stuckJobsCount) . ', stuck for at least '
                . config('stuck-jobs-notifier.hours_limit') . ' hours, detected at ' . config('app.url') . '.',
        ])->header('Content-Type', 'application/json');
    }
}
