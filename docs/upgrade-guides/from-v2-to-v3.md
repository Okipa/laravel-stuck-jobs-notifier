# Upgrade from v1 to V2

Follow the steps below to upgrade the package.

## Laravel and PHP supported versions update

This package does now support PHP 8.0, PHP 8.1, Laravel 8 and Laravel 9.

PHP 7.4 and Laravel 7.0 are no longer supported in v3.

## How to upgrade ?

Just bump the version the package to version `^3.0`.

You also may have to bump the package optional dependencies listed in the [installation documentation part](../../README.md#installation) if you have installed them:
* `laravel-notification-channels/webhook` should be upgrade to version `^2.3`
* `laravel/slack-notification-channel` should be upgrade to version `^2.4`

## See all changes

See all change with the [comparison tool](https://github.com/Okipa/laravel-stuck-job-notifier/compare/2.1.0...3.0.0).

## Undocumented changes

If you see any forgotten and undocumented change, please submit a PR to add them to this upgrade guide.
