<?php

namespace Okipa\LaravelStuckJobsNotifier\Test;

use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [StuckJobsNotifierServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
