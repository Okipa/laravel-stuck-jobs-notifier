<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification;

class StuckJobsNotifier
{
    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function notify(): void
    {
        if ($this->isAllowedToRun()) {
            $stuckJobs = $this->getStuckFailedJobs();
            if ($stuckJobs->isNotEmpty()) {
                $notifiable = app(config('failed-jobs-notifier.notifiable'));
                $notification = $this->getNotification($stuckJobs);
                $notifiable->notify($notification);
            }
        }
    }

    /**
     * @return bool
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function isAllowedToRun(): bool
    {
        $allowedToRun = config('failed-jobs-notifier.allowedToRun');
        if (is_callable($allowedToRun)) {
            return $allowedToRun();
        } elseif (is_bool($allowedToRun)) {
            return $allowedToRun;
        }
        throw new InvalidAllowedToRun('The allowedToRun config value is not a boolean or a callable.');
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getStuckFailedJobs(): Collection
    {
        $this->checkFailedJobsTableExists();
        $hoursLimit = $this->getDaysLimit();
        $dateLimit = Carbon::now()->subHours($hoursLimit);

        return DB::table('failed_jobs')->where('failed_at', '<=', $dateLimit)->get();
    }

    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     */
    public function checkFailedJobsTableExists(): void
    {
        if (! Schema::hasTable('failed_jobs')) {
            throw new InexistentFailedJobsTable('No failed_jobs table has been found. Please check Laravel '
                . 'documentation to set it up : https://laravel.com/docs/queues#dealing-with-failed-jobs.');
        }
    }

    /**
     * @return int
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     */
    public function getDaysLimit(): int
    {
        $hoursLimit = config('failed-jobs-notifier.hoursLimit');
        if (! is_int($hoursLimit)) {
            throw new InvalidHoursLimit('The configured hours limit should be an integer.');
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
        $notification = app(config('failed-jobs-notifier.notification'), ['stuckJobs' => $stuckJobs]);
        if (! $notification instanceof Notification || ! is_subclass_of($notification, Notification::class)) {
            throw new InvalidNotification('The configured notification does not extend ' . Notification::class);
        }

        return $notification;
    }
}
