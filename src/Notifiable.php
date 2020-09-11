<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string
    {
        return config('stuck-jobs-notifier.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('stuck-jobs-notifier.slack.webhookUrl');
    }

    public function routeNotificationForWebhook(): string
    {
        return config('stuck-jobs-notifier.webhook.url');
    }

    public function getKey(): int
    {
        return 1;
    }
}
