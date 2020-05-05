<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Dummy;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class AnotherNotifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail(): string
    {
        return 'john@example.com';
    }

    public function routeNotificationForSlack(): string
    {
        return '';
    }

    public function routeNotificationForWebhook(): string
    {
        return '';
    }

    public function getKey()
    {
        return 1;
    }
}
