<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    /**
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return config('failed-jobs-notifier.mail.to');
    }

    /**
     * @return string
     */
    public function routeNotificationForSlack(): string
    {
        return config('failed-jobs-notifier.slack.webhookUrl');
    }

    /**
     * @return string
     */
    public function routeNotificationForWebhook(): string
    {
        return config('failed-jobs-notifier.webhook.url');
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return 1;
    }
}
