<?php

namespace Okipa\LaravelStuckJobsNotifier\Notifications;

use Carbon\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification as IlluminateNotification;
use Illuminate\Support\Collection;
use NotificationChannels\Webhook\WebhookMessage;

class JobsAreStuck extends IlluminateNotification
{
    protected Collection $stuckJobs;

    protected int $stuckJobsCount;

    public function __construct(Collection $stuckJobs)
    {
        $this->stuckJobs = $stuckJobs;
        $this->stuckJobsCount = $stuckJobs->count();
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
            ->subject(trans_choice(
                '{1}:app - :env: :count job is stuck in queue|[2,*]:app - :env: :count jobs are stuck in queue',
                $this->stuckJobsCount,
                [
                    'app' => config('app.name'),
                    'env' => config('app.env'),
                    'count' => $this->stuckJobsCount,
                ]
            ))
            ->line(trans_choice(
                '{1}We have detected that :count job is stuck in [:app - :env](:url) queue since :date.'
                . '|[2,*]We have detected that :count jobs are stuck in [:app - :env](:url) queue since :date.',
                $this->stuckJobsCount,
                [
                    'count' => $this->stuckJobsCount,
                    'date' => Carbon::parse($this->stuckJobs->min('failed_at'))->format('d/m/Y - H:i:s'),
                    'app' => config('app.name'),
                    'env' => config('app.env'),
                    'url' => config('app.url'),
                ]
            ))
            ->line('Please check your stuck jobs connecting to your server and executing the '
                . '« php artisan queue:failed » command.');
    }

    /**
     * Get the slack representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)->error()->content('⚠ ' . trans_choice(
            '{1}`:app - :env` :count job job is stuck in :url queue since :date.'
                . '|[2,*]`:app - :env` :count jobs are stuck in :url queue since :date.',
            $this->stuckJobsCount,
            [
                    'app' => config('app.name'),
                    'env' => config('app.env'),
                    'count' => $this->stuckJobsCount,
                    'url' => config('app.url'),
                    'date' => Carbon::parse($this->stuckJobs->min('failed_at'))->format('d/m/Y - H:i:s'),
                ]
        ));
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
            'text' => '⚠ ' . trans_choice(
                '{1}`:app - :env` :count job job is stuck in :url queue since :date.'
                    . '|[2,*]`:app - :env` :count jobs are stuck in :url queue since :date.',
                $this->stuckJobsCount,
                [
                        'app' => config('app.name'),
                        'env' => config('app.env'),
                        'count' => $this->stuckJobsCount,
                        'url' => config('app.url'),
                        'date' => Carbon::parse($this->stuckJobs->min('failed_at'))->format('d/m/Y - H:i:s'),
                    ]
            ),
        ])->header('Content-Type', 'application/json');
    }
}
