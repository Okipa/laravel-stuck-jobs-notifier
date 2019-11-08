<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Illuminate\Support\ServiceProvider;
use Okipa\LaravelFailedJobsNotifier\Commands\NotifyFailedJobs;

class FailedJobsReporterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NotifyFailedJobs::class]);
        }
        $this->publishes([
            __DIR__ . '/../config/failed-jobs-notifier.php' => config_path('failed-jobs-notifier.php'),
        ], 'failed-jobs-notifier:config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/failed-jobs-notifier.php', 'failed-jobs-notifier');
    }
}
