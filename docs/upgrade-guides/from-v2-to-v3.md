# Upgrade from v2 to v3

The v3 embeds some breaking changes.

Follow the steps below to upgrade the package.

## Package name change

The package name has changed to better fits with the real features it provides.

The name changed from `laravel-failed-jobs-notifier` to `laravel-stuck-jobs-notifier`.

As so, the old package has been archived, marked as abandoned and now suggest to use the new one.

## Command signature change

The command signature changed from `queue:failed:notify` to `queue:stuck:notify`;

## Config changes

The config file name has changed from `failed-jobs-notifier.php` to `stuck-jobs-notifier.php`.

Some config variable names have changed have been made. If you customized it, you should [re-publish it](../../README.md#configuration) and reapply your changes.

## Classes and exception name changes

In case of customizations, please note that the following classes and exception names have changed:
* 

## See all changes

See all change with the [comparison tool](https://github.com/Okipa/laravel-table/compare/1.5.0...2.0.0).

## Undocumented changes

If you see any forgotten and undocumented change, please submit a PR to add them to this upgrade guide.
