<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string
    {
        return config('failed-jobs-notifier.mail.to');
    }

    public function routeNotificationForSlack(): string
    {
        return config('failed-jobs-notifier.slack.webhookUrl');
    }

    public function routeNotificationForWebhook(): string
    {
        return config('failed-jobs-notifier.webhook.url');
    }

    public function getKey(): int
    {
        return 1;
    }
}
