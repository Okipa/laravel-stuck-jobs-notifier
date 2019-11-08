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
    protected $description = 'Notify when failed jobs are stuck for a certain amount of time.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification
     * @throws \Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidProcessAllowedToRun
     */
    public function handle()
    {
        (new FailedJobsNotifier)->notify();
    }
}
