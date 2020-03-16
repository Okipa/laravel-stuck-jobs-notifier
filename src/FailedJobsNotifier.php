<?php

namespace Okipa\LaravelFailedJobsNotifier;

use Carbon\Carbon;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification;

class FailedJobsNotifier
{
    /**
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function notify(): void
    {
        if ($this->isAllowedToRun()) {
            $stuckFailedJobs = $this->getStuckFailedJobs();
            if ($stuckFailedJobs->isNotEmpty()) {
                $notifiable = app(config('failed-jobs-notifier.notifiable'));
                $notification = $this->getNotification($stuckFailedJobs);
                $notifiable->notify($notification);
            }
        }
    }

    /**
     * @return bool
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidAllowedToRun
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
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit
     */
    public function getStuckFailedJobs(): Collection
    {
        $this->checkFailedJobsTableExists();
        $daysLimit = $this->getDaysLimit();
        $dateLimit = Carbon::now()->subDays($daysLimit);

        return DB::table('failed_jobs')->where('failed_at', '<=', $dateLimit)->get();
    }

    /**
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable
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
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit
     */
    public function getDaysLimit(): int
    {
        $daysLimit = config('failed-jobs-notifier.daysLimit');
        if (! is_int($daysLimit)) {
            throw new InvalidDaysLimit('The configured day limit is not an integer.');
        }

        return $daysLimit;
    }

    /**
     * @param \Illuminate\Support\Collection $stuckFailedJobs
     *
     * @return \Illuminate\Notifications\Notification
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification
     */
    public function getNotification(Collection $stuckFailedJobs): Notification
    {
        /** @var \Okipa\LaravelFailedJobsNotifier\Notification|mixed $notification */
        $notification = app(config('failed-jobs-notifier.notification'), ['stuckFailedJobs' => $stuckFailedJobs]);
        if (! $notification instanceof Notification || ! is_subclass_of($notification, Notification::class)) {
            throw new InvalidNotification('The configured notification does not extend ' . Notification::class);
        }

        return $notification;
    }
}
