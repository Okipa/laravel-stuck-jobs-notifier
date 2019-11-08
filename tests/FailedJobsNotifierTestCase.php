<?php

namespace Okipa\LaravelBootstrapComponents\Test;

use Faker\Factory;
use Okipa\LaravelFailedJobsNotifier\FailedJobsReporterServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class BootstrapComponentsTestCase extends TestCase
{
    protected $faker;

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue.default', 'sync');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [FailedJobsReporterServiceProvider::class];
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
