<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Support\ServiceProvider;
use Okipa\LaravelStuckJobsNotifier\Commands\NotifyStuckJobs;

class StuckJobsNotifierServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([NotifyStuckJobs::class]);
        }
        $this->publishes([
            __DIR__ . '/../config/stuck-jobs-notifier.php' => config_path('stuck-jobs-notifier.php'),
        ], 'stuck-jobs-notifier:config');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/stuck-jobs-notifier.php', 'stuck-jobs-notifier');
    }
}
