<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Support\ServiceProvider;
use Okipa\LaravelStuckJobsNotifier\Commands\NotifyStuckJobs;
use Okipa\LaravelStuckJobsNotifier\Commands\SimulateStuckJobs;

class StuckJobsNotifierServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NotifyStuckJobs::class, SimulateStuckJobs::class]);
        }
        $this->publishes([
            __DIR__ . '/../config/stuck-jobs-notifier.php' => config_path('stuck-jobs-notifier.php'),
        ], 'stuck-jobs-notifier:config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stuck-jobs-notifier.php', 'stuck-jobs-notifier');
    }
}
