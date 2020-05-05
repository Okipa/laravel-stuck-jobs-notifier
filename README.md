# Get notified when stuck jobs are detected

[![Source Code](https://img.shields.io/badge/source-okipa/laravel--stuck--jobs--notifier-blue.svg)](https://github.com/Okipa/laravel-stuck-jobs-notifier)
[![Latest Version](https://img.shields.io/github/release/okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://github.com/Okipa/laravel-stuck-jobs-notifier/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://packagist.org/packages/okipa/laravel-stuck-jobs-notifier)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Build Status](https://travis-ci.org/Okipa/laravel-stuck-jobs-notifier.svg?branch=master)](https://travis-ci.org/Okipa/laravel-stuck-jobs-notifier)
[![Coverage Status](https://coveralls.io/repos/github/Okipa/laravel-stuck-jobs-notifier/badge.svg?branch=master)](https://coveralls.io/github/Okipa/laravel-stuck-jobs-notifier?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/Okipa/laravel-stuck-jobs-notifier.svg?style=flat-square)](https://scrutinizer-ci.com/g/Okipa/laravel-stuck-jobs-notifier/?branch=master)

Get notified and execute any PHP callback when some jobs are stuck in your `failed_jobs` table for a number of hours of your choice.
  
Notifications can be sent by mail, Slack and webhooks (chats often provide a webhook API).

## Compatibility

| Laravel version | PHP version | Package version |
|---|---|---|
| ^6.0 | ^7.4 | ^3.0 |
| ^5.8 | ^7.2 | ^2.0 |
| ^5.5 | ^7.1 | ^1.0 |

## Upgrade guide

* [From V2 to V3](/docs/upgrade-guides/from-v2-to-v3.md)

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

Install the package with composer:

```bash
composer require "okipa/laravel-stuck-jobs-notifier:^3.0"
```

## Configuration
  
Publish the package configuration: 

```bash
php artisan vendor:publish --tag=stuck-jobs-notifier:config
```

## Usage

Just add this command in the `schedule()` method of your `\App\Console\Kernel` class :

```php
$schedule->command('queue:stuck:notify')->twiceDaily(9, 15);
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
