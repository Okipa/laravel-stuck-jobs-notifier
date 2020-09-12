# Get notified when stuck jobs are detected

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--stuck--jobs--notifier-blue.svg)](https://github.com/Okipa/laravel-stuck-jobs-notifier)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://github.com/Okipa/laravel-stuck-jobs-notifier/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-stuck-jobs-notifier)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build status](https://github.com/Okipa/laravel-stuck-jobs-notifier/workflows/CI/badge.svg)](https://github.com/Okipa/laravel-stuck-jobs-notifier/actions)
[![Coverage Status](https://coveralls.io/repos/github/Okipa/laravel-stuck-jobs-notifier/badge.svg?branch=master)](https://coveralls.io/github/Okipa/laravel-stuck-jobs-notifier?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/Okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://scrutinizer-ci.com/g/Okipa/laravel-stuck-jobs-notifier/?branch=master)

Get notified and execute PHP callback when you have stuck jobs for a defined number of hours.
  
Notifications can be sent by mail, Slack and webhooks (chats often provide a webhook API).

## Compatibility

| Laravel version | PHP version | Package version |
|---|---|---|
| ^6.0 | ^7.4 | ^1.0 |

## Upgrade guide

* [From okipa/failed-jobs-notifier](/docs/upgrade-guides/from-failed-job-notifier.md)

## Table of Contents
* [Installation](#installation)
* [Configuration](#configuration)
* [Translations](#translations)
* [Usage](#usage)
* [Testing](#testing)
* [Changelog](#changelog)
* [Contributing](#contributing)
* [Credits](#credits)
* [Licence](#license)

## Installation

Install the package with composer:

```bash
composer require "okipa/laravel-stuck-jobs-notifier:^1.0"
```

In case you want to use `Slack` notifications you'll also have to install:

```bash
composer require guzzlehttp/guzzle
```

## Configuration
  
Publish the package configuration: 

```bash
php artisan vendor:publish --tag=stuck-jobs-notifier:config
```

## Translations

All words and sentences used in this package are translatable.

See how to translate them on the Laravel official documentation: https://laravel.com/docs/localization#using-translation-strings-as-keys.

Here is the list of the words and sentences available for translation by default:

```text
* {1}[:app - :env] :count job is stuck in queue|[2,*][:app - :env] :count jobs are stuck in queue
* {1}We have detected that :count job is stuck in the [:app - :env](:url) queue since the :day at :hour.|[2,*]We have detected that :count jobs are stuck in the [:app - :env](:url) queue since the :day at :hour.'
* Please check your stuck jobs connecting to your server and executing the "php artisan queue:failed" command.
* {1}`[:app - :env]` :count job is stuck in the :url queue since the :day at :hour.|[2,*]`[:app - :env]` :count jobs are stuck in the :url queue since the :day at :hour.
* Notification test:
* Exception test:
```

## Usage

Just add this command in the `schedule()` method of your `\App\Console\Kernel` class:

```php
$schedule->command('queue:stuck:notify')->twiceDaily(10, 16);
```

And you will be notified as soon as some jobs will be stuck in the `failed_jobs` table for the number of days you configured.

To check if everything is correctly configured, you can simulate stuck jobs detection:

```bash
php artisan queue:stuck:simulate
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

* [Arthur LORENT](https://github.com/okipa)
* [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
