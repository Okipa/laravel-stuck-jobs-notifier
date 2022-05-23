<?php

namespace Okipa\LaravelStuckJobsNotifier\Test;

use Faker\Factory;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

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
