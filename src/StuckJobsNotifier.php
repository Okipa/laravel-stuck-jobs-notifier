<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidCallback;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification;

class StuckJobsNotifier
{
    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidCallback
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification
     */
    public function notify(): void
    {
        if ($this->isAllowedToRun()) {
            $stuckJobs = $this->getStuckFailedJobs();
            if ($stuckJobs->isNotEmpty()) {
                $notifiable = app(config('stuck-jobs-notifier.notifiable'));
                $notification = $this->getNotification($stuckJobs);
                $notifiable->notify($notification);
                $this->executeCallback($stuckJobs);
            }
        }
    }

    /**
     * @return bool
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function isAllowedToRun(): bool
    {
        $allowedToRun = config('stuck-jobs-notifier.allowed_to_run');
        if (is_callable($allowedToRun)) {
            return $allowedToRun();
        } elseif (is_bool($allowedToRun)) {
            return $allowedToRun;
        }
        throw new InvalidAllowedToRun('The `allowed_to_run` config is not a boolean or a callable.');
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getStuckFailedJobs(): Collection
    {
        $this->checkFailedJobsTableExists();
        $dateLimit = $this->getDateLimit();

        return DB::table('failed_jobs')->where('failed_at', '<=', $dateLimit)->get();
    }

    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     */
    public function checkFailedJobsTableExists(): void
    {
        if (! Schema::hasTable('failed_jobs')) {
            throw new InexistentFailedJobsTable('No `failed_jobs table has been found. Please check Laravel '
                . 'documentation to set it up : https://laravel.com/docs/queues#dealing-with-failed-jobs.');
        }
    }

    /**
     * @return \Carbon\Carbon
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getDateLimit(): Carbon
    {
        $hoursLimit = $this->getHoursLimit();

        return Carbon::now()->subHours($hoursLimit);
    }

    /**
     * @return int
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getHoursLimit(): int
    {
        $hoursLimit = config('stuck-jobs-notifier.hours_limit');
        if (! is_int($hoursLimit)) {
            throw new InvalidHoursLimit('The `hours_limit` config is not an integer.');
        }

        return $hoursLimit;
    }

    /**
     * @param \Illuminate\Support\Collection $stuckJobs
     *
     * @return \Illuminate\Notifications\Notification
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification
     */
    public function getNotification(Collection $stuckJobs): Notification
    {
        /** @var \Okipa\LaravelStuckJobsNotifier\Notification|mixed $notification */
        $notification = app(config('stuck-jobs-notifier.notification'), ['stuckJobs' => $stuckJobs]);
        if (! $notification instanceof Notification || ! is_subclass_of($notification, Notification::class)) {
            throw new InvalidNotification('The `notification` config does not extend ' . Notification::class);
        }

        return $notification;
    }

    public function executeCallback(Collection $stuckJobs): void
    {
        $callback = config('stuck-jobs-notifier.callback');
        if ($callback) {
            new $callback($stuckJobs);
        }
    }
}
