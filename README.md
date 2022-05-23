![Laravel Stuck Jobs Notifier](/docs/laravel-stuck-jobs-notifier.png)
<p style="text-align: center;">
    <a href="https://github.com/Okipa/laravel-stuck-jobs-notifier/releases" title="Latest Stable Version">
        <img src="https://img.shields.io/github/release/Okipa/laravel-stuck-jobs-notifier.svg?style=flat-square" alt="Latest Stable Version">
    </a>
    <a href="https://packagist.org/packages/Okipa/laravel-stuck-jobs-notifier" title="Total Downloads">
        <img src="https://img.shields.io/packagist/dt/okipa/laravel-stuck-jobs-notifier.svg?style=flat-square" alt="Total Downloads">
    </a>
    <a href="https://github.com/Okipa/laravel-stuck-jobs-notifier/actions" title="Build Status">
        <img src="https://github.com/Okipa/laravel-stuck-jobs-notifier/workflows/CI/badge.svg" alt="Build Status">
    </a>
    <a href="https://coveralls.io/github/Okipa/laravel-stuck-jobs-notifier?branch=master" title="Coverage Status">
        <img src="https://coveralls.io/repos/github/Okipa/laravel-stuck-jobs-notifier/badge.svg?branch=master" alt="Coverage Status">
    </a>
    <a href="/LICENSE.md" title="License: MIT">
        <img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License: MIT">
    </a>
</p>

Get notified and execute PHP callback when you have stuck jobs for a defined number of hours.
  
Notifications can be sent by mail, Slack and webhooks (chats often provide a webhook API).

Found this package helpful? Please consider supporting my work!

[![Donate](https://img.shields.io/badge/Buy_me_a-Ko--fi-ff5f5f.svg)](https://ko-fi.com/arthurlorent)
[![Donate](https://img.shields.io/badge/Donate_on-PayPal-green.svg)](https://paypal.me/arthurlorent)

## Compatibility

| Laravel version | PHP version | Package version |
|---|---|---|
| ^8.0 &#124; ^9.0 | ^8.0 &#124; ^8.1 | ^3.0 |
| ^7.0 &#124; ^8.0 | ^7.4 &#124; ^8.0 | ^2.0 |
| ^6.0 &#124; ^7.0 | ^7.4 | ^1.0 |

## Upgrade guide

* From v2 to v3 : ``
* [From v1 to V2](/docs/upgrade-guides/from-v1-to-v2.md)
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
composer require okipa/laravel-stuck-jobs-notifier
```

If you intend to send `Slack` notifications you will have to install:

* https://github.com/laravel/slack-notification-channel

```bash
composer require laravel/slack-notification-channel
```

If you intend to send `webhook` notifications you will have to install:

* https://github.com/laravel-notification-channels/webhook

```bash
composer require laravel-notification-channels/webhook
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
* {1}We have detected that :count job is stuck in the [:app - :env](:url) queue since the :day at :hour.|[2,*]We have detected that :count jobs are stuck in the [:app - :env](:url) queue since the :day at :hour.
* Please check your stuck jobs connecting to your server and executing the "php artisan queue:failed" command.
* {1}`[:app - :env]` :count job is stuck in the :url queue since the :day at :hour.|[2,*]`[:app - :env]` :count jobs are stuck in the :url queue since the :day at :hour.
* {1}:count job is stuck in queue since the :day at :hour.|[2,*]:count jobs are stuck in queue since the :day at :hour.
* Notification test:
* Exception test:
```

## Usage

Just add this command in the `schedule()` method of your `\App\Console\Kernel` class:

```php
$schedule->command('queue:stuck:notify')->twiceDaily(10, 16);
```

And you will be notified as soon as some jobs will be stuck in the `failed_jobs` table for the number of days you configured.

Once everything has been set up, you can check if the configuration is correct by simulating stuck jobs detection:

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
