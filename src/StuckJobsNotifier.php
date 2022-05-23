<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Callbacks\OnStuckJobs;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Notifications\JobsAreStuck;

class StuckJobsNotifier
{
    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected
     */
    public function notify(): void
    {
        if ($this->isAllowedToRun()) {
            $stuckJobs = $this->getStuckFailedJobs();
            if ($stuckJobs->isNotEmpty()) {
                $notification = $this->getNotification($stuckJobs);
                $this->getNotifiable()->notify($notification);
                $callback = $this->getCallback();
                if ($callback) {
                    $callback($stuckJobs);
                }
            }
        }
    }

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun */
    public function isAllowedToRun(): bool
    {
        $allowedToRun = config('stuck-jobs-notifier.allowed_to_run');
        if (is_callable($allowedToRun)) {
            return $allowedToRun();
        }
        if (is_bool($allowedToRun)) {
            return $allowedToRun;
        }
        throw new InvalidAllowedToRun('The `stuck-jobs-notifier.allowed_to_run` config is not a boolean or '
            . 'a callable.');
    }

    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getStuckFailedJobs(): Collection
    {
        $this->checkFailedJobsTableExists();
        $dateLimit = $this->getDateLimit();

        return DB::table('failed_jobs')->where('failed_at', '<=', $dateLimit)->get();
    }

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable */
    public function checkFailedJobsTableExists(): void
    {
        if (! Schema::hasTable('failed_jobs')) {
            throw new InexistentFailedJobsTable('No `failed_jobs table has been found. Please check Laravel '
                . 'documentation to set it up : https://laravel.com/docs/queues#dealing-with-failed-jobs.');
        }
    }

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit */
    public function getDateLimit(): CarbonInterface
    {
        $hoursLimit = $this->getHoursLimit();

        return Date::now()->subHours($hoursLimit);
    }

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit */
    public function getHoursLimit(): int
    {
        $hoursLimit = config('stuck-jobs-notifier.hours_limit');
        if (! is_int($hoursLimit)) {
            throw new InvalidHoursLimit('The `stuck-jobs-notifier.hours_limit` config is not an integer.');
        }

        return $hoursLimit;
    }

    public function getNotification(Collection $stuckJobs, bool $isTesting = false): JobsAreStuck
    {
        return app(config('stuck-jobs-notifier.notification'), compact('stuckJobs', 'isTesting'));
    }

    public function getNotifiable(): Notifiable
    {
        return app(config('stuck-jobs-notifier.notifiable'));
    }

    public function getCallback(): ?OnStuckJobs
    {
        $callback = config('stuck-jobs-notifier.callback');

        return $callback ? app($callback) : null;
    }
}
