<?php

namespace Okipa\LaravelFailedJobsNotifier\Commands;

use Illuminate\Console\Command;
use Okipa\LaravelFailedJobsNotifier\FailedJobsNotifier;

class NotifyFailedJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:failed:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify when failed jobs are stuck for a defined number of days.';

    /**
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function handle(): void
    {
        (new FailedJobsNotifier)->notify();
    }
}
