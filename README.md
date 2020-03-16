# Get notified when failed jobs are stuck

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--failed--jobs--notifier-blue.svg)](https://github.com/Okipa/laravel-failed-jobs-notifier)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-failed-jobs-notifier.svg?style=flat-square)](https://github.com/Okipa/laravel-failed-jobs-notifier/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-failed-jobs-notifier.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-failed-jobs-notifier)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://travis-ci.org/Okipa/laravel-failed-jobs-notifier.svg?branch=master)](https://travis-ci.org/Okipa/laravel-failed-jobs-notifier)
[![Coverage Status](https://coveralls.io/repos/github/Okipa/laravel-failed-jobs-notifier/badge.svg?branch=master)](https://coveralls.io/github/Okipa/laravel-failed-jobs-notifier?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/Okipa/laravel-failed-jobs-notifier.svg?style=flat-square)](https://scrutinizer-ci.com/g/Okipa/laravel-failed-jobs-notifier/?branch=master)

Get notified when some jobs are stuck in your `failed_jobs` table for a number of days of your choice.  
Notifications can be sent by mail, Slack and webhooks (chats often provide a webhook API).  

## Compatibility

| Laravel version | PHP version | Package version |
|---|---|---|
| ^5.8 | ^7.2 | ^2.0 |
| ^5.5 | ^7.1 | ^1.0 |

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [Licence](#license)

## Installation

- Install the package with composer :

```bash
composer require "okipa/laravel-failed-jobs-notifier:^1.0"
```

- Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

If you don't use auto-discovery or if you use a Laravel 5.4- version, add the package service provider in the `register()` method from your `app/Providers/AppServiceProvider.php` :
```php
// laravel bootstrap components
// https://github.com/Okipa/laravel-failed-jobs-notifier
$this->app->register(\Okipa\LaravelFailedJobsNotifier\FailedJobsReporterServiceProvider::class);
```

## Configuration
  
Publish the package configuration and override the available config values : 

```bash
php artisan vendor:publish --tag=failed-jobs-notifier:config
```

## Usage

Just add this command in the `schedule()` method of your `\App\Console\Kernel` class :

```php
$schedule->command('queue:failed:notify')->twiceDaily(9, 15);
```

And you will be notified as soon as some jobs will be stuck in the `failed_jobs` table for the number of days you configured.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Arthur LORENT](https://github.com/okipa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
