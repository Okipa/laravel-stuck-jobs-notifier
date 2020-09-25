<?php

namespace Okipa\LaravelStuckJobsNotifier\Test;

use Faker\Factory;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifierServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class FailedJobsNotifierTestCase extends TestCase
{
    protected $faker;

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
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

    /**
     * Get package providers.
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [StuckJobsNotifierServiceProvider::class];
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->faker = Factory::create();
    }
}
