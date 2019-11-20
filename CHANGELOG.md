# Changelog

## [1.0.3](https://github.com/Okipa/laravel-failed-jobs-notifier/releases/tag/1.0.3)

2019-11-20

- Improved the stuck failed jobs identification in order to get them as soon as they are stuck for the configured number of days (or more).

## [1.0.2](https://github.com/Okipa/laravel-failed-jobs-notifier/releases/tag/1.0.2)

2019-11-12

- Updated the default config `allowedToRun` value, in order to avoid errors on `php artisan optimize` run.

## [1.0.1](https://github.com/Okipa/laravel-failed-jobs-notifier/releases/tag/1.0.1)

2019-11-12

- Simplified `processAllowedToRun` config label to `allowedToRun`.
- Added the `config('app.env')` data to the default notification messages for the different channels.
- Improved test coverage.

## [1.0.0](https://github.com/Okipa/laravel-failed-jobs-notifier/releases/tag/1.0.0)

2019-11-08

- First release.
