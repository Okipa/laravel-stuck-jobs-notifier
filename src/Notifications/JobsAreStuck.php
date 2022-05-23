<?php

namespace Okipa\LaravelStuckJobsNotifier\Notifications;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\Webhook\WebhookMessage;

class JobsAreStuck extends Notification
{
    protected int $stuckJobsCount;

    protected CarbonInterface $stuckSince;

    public function __construct(protected Collection $stuckJobs, protected bool $isTesting)
    {
        $this->stuckJobsCount = $stuckJobs->count();
        $this->stuckSince = Carbon::parse($this->stuckJobs->min('failed_at'));
    }

    public function via(): array
    {
        return config('stuck-jobs-notifier.channels');
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())->level('error')
            ->subject(($this->isTesting ? __('Notification test:') . ' ' : '')
                . trans_choice(
                    '{1}[:app - :env] :count job is stuck in queue|[2,*][:app - :env] :count jobs are stuck in queue',
                    $this->stuckJobsCount,
                    [
                        'app' => config('app.name'),
                        'env' => config('app.env'),
                        'count' => $this->stuckJobsCount,
                    ]
                ))
            ->line(($this->isTesting ? __('Notification test:') . ' ' : '')
                . trans_choice(
                    '{1}We have detected that :count job is stuck in the [:app - :env](:url) queue '
                    . 'since the :day at :hour.'
                    . '|[2,*]We have detected that :count jobs are stuck in the [:app - :env](:url) queue '
                    . 'since the :day at :hour.',
                    $this->stuckJobsCount,
                    [
                        'count' => $this->stuckJobsCount,
                        'app' => config('app.name'),
                        'env' => config('app.env'),
                        'url' => config('app.url'),
                        'day' => $this->stuckSince->format('d/m/Y'),
                        'hour' => $this->stuckSince->format('H:i:s'),
                    ]
                ))
            ->line((string) __('Please check your stuck jobs connecting to your server and executing the '
                . '"php artisan queue:failed" command.'));
    }

    /**
     * Get the slack representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())->error()->content('⚠ '
            . ($this->isTesting ? __('Notification test:') . ' ' : '')
            . trans_choice(
                '{1}`[:app - :env]` :count job is stuck in the :url queue since the :day at :hour.'
                . '|[2,*]`[:app - :env]` :count jobs are stuck in the :url queue since the :day at :hour.',
                $this->stuckJobsCount,
                [
                    'app' => config('app.name'),
                    'env' => config('app.env'),
                    'count' => $this->stuckJobsCount,
                    'url' => config('app.url'),
                    'day' => $this->stuckSince->format('d/m/Y'),
                    'hour' => $this->stuckSince->format('H:i:s'),
                ]
            ));
    }

    public function toWebhook(): WebhookMessage
    {
        // Rocket chat webhook example.
        return WebhookMessage::create()->data([
            'text' => '⚠ '
                . ($this->isTesting ? (string) __('Notification test:') . ' ' : '')
                . trans_choice(
                    '{1}`[:app - :env]` :count job is stuck in the :url queue since the :day at :hour.'
                    . '|[2,*]`[:app - :env]` :count jobs are stuck in the :url queue since the :day at :hour.',
                    $this->stuckJobsCount,
                    [
                        'app' => config('app.name'),
                        'env' => config('app.env'),
                        'count' => $this->stuckJobsCount,
                        'url' => config('app.url'),
                        'day' => $this->stuckSince->format('d/m/Y'),
                        'hour' => $this->stuckSince->format('H:i:s'),
                    ]
                ),
        ])->header('Content-Type', 'application/json');
    }
}
