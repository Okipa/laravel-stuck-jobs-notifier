# Changelog

## [2.0.0](https://github.com/Okipa/laravel-failed-jobs-notifier/compare/1.1.0...2.0.0)

2020-03-16

* Added Laravel 7 support.
* Upgraded `laravel-notification-channels/webhook` dependency to v2.
* Renamed `failed-jobs-notifier.slack.webhook_url` config to `failed-jobs-notifier.slack.webhookUrl`.

## [1.1.0](https://github.com/Okipa/laravel-failed-jobs-notifier/compare/1.0.3...1.1.0)

2020-03-03

* Added php7.4 support.
* Added Laravel 7 support.
* Dropped support for Laravel versions under 5.8.

## [1.0.3](https://github.com/Okipa/laravel-failed-jobs-notifier/compare/1.0.2...1.0.3)

2019-11-20

* Improved the stuck failed jobs identification in order to get them as soon as they are stuck for the configured number of days (or more).

## [1.0.2](https://github.com/Okipa/laravel-failed-jobs-notifier/compare/1.0.1...1.0.2)

2019-11-12

* Updated the default config `allowedToRun` value, in order to avoid errors on `php artisan optimize` run.

## [1.0.1](https://github.com/Okipa/laravel-failed-jobs-notifier/compare/1.0.0...1.0.1)

2019-11-12

* Simplified `processAllowedToRun` config label to `allowedToRun`.
* Added the `config('app.env')` data to the default notification messages for the different channels.
* Improved test coverage.

## [1.0.0](https://github.com/Okipa/laravel-failed-jobs-notifier/releases/tag/1.0.0)

2019-11-08

* First release.
