<?php

namespace Okipa\LaravelStuckJobsNotifier\Commands;

use Illuminate\Console\Command;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;

class NotifyStuckJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:stuck:notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify when failed jobs are stuck for a defined number of hours.';

    /**
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun
     */
    public function handle(): void
    {
        (new StuckJobsNotifier)->notify();
    }
}
