<?php

use NotificationChannels\Webhook\WebhookChannel;

return [

    /*
     * The number of hours from which the failed jobs are considered as stuck.
     */
    'hours_limit' => 2,

    /*
     * The notifiable to which the notification will be sent. The default
     * notifiable will use the mail and slack configuration specified
     * in this config file.
     */
    'notifiable' => Okipa\LaravelStuckJobsNotifier\Notifiable::class,

    /*
     * The notification that will be sent when stuck jobs are detected.
     */
    'notification' => Okipa\LaravelStuckJobsNotifier\Notification::class,

    /*
     * The callback that will be executed when stuck jobs are detected.
     * Any class can be called here and will receive a $stuckJobs collection in its constructor.
     * Can be set to null if you do not want any callback to be executed.
     */
    'callback' => Okipa\LaravelStuckJobsNotifier\Callback::class,

    /*
    * You can pass a boolean or a callable to authorize or block the notification process.
    * If the boolean or the callable return false, no notification will be sent.
    */
    'allowed_to_run' => env('APP_ENV') !== 'local',

    /*
     * The channels to which the notification will be sent.
     */
    'channels' => ['mail', 'slack', WebhookChannel::class],

    'mail' => ['to' => 'email@example.test'],

    'slack' => ['webhookUrl' => 'https://your-slack-webhook.slack.com'],

    // rocket chat webhook example
    'webhook' => ['url' => 'https://rocket.chat/hooks/1234/5678'],

];
