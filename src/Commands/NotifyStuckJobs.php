<?php

namespace Okipa\LaravelStuckJobsNotifier\Commands;

use Illuminate\Console\Command;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;

class NotifyStuckJobs extends Command
{
    /** @var string */
    protected $signature = 'queue:stuck:notify';

    /** @var string */
    protected $description = 'Notify when failed jobs are stuck for a defined number of hours.';

    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected
     */
    public function handle(): void
    {
        app(StuckJobsNotifier::class)->notify();
    }
}
