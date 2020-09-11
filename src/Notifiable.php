<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string
    {
        /** @var string $email */
        $email = config('stuck-jobs-notifier.mail.to');

        return $email;
    }

    public function routeNotificationForSlack(): string
    {
        /** @var string $webhookUrl */
        $webhookUrl = config('stuck-jobs-notifier.slack.webhookUrl');

        return $webhookUrl;
    }

    public function routeNotificationForWebhook(): string
    {
        /** @var string $webhookUrl */
        $webhookUrl = config('stuck-jobs-notifier.webhook.url');

        return $webhookUrl;
    }

    public function getKey(): int
    {
        return 1;
    }
}
