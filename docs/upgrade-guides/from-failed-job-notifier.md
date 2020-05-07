# Upgrade from [okipa/failed-job-notifier](https://github.com/Okipa/laravel-failed-jobs-notifier)

Follow the steps below to upgrade the package.

## Package name change

The package name has changed to better fits with the real features it provides.

The name changed from `laravel-failed-jobs-notifier` to `laravel-stuck-jobs-notifier`.

As so, the old package has been archived, marked as abandoned and now suggest using the new one.

## Command signature change

The command signature changed from `queue:stuck:notify` to `queue:stuck:notify`;

## Config changes

The config file name has changed from `failed-jobs-notifier.php` to `stuck-jobs-notifier.php`.

Some config keys have changed. If you customized it, you should [re-publish it](../../README.md#configuration) and reapply your changes.

## Classes and exception name changes

In case of customizations, please note that the following class and exception names have changed:
* `FailedJobsNotifier` class has been renamed `StuckJobsNotifier`.
* `InvalidDaysLimit` exception has been renamed `InvalidHoursLimit`.

## See all changes

See all change with the [comparison tool](https://github.com/Okipa/laravel-table/compare/1.5.0...2.0.0).

## Undocumented changes

If you see any forgotten and undocumented change, please submit a PR to add them to this upgrade guide.
