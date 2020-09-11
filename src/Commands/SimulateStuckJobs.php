<?php

namespace Okipa\LaravelStuckJobsNotifier\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;

class SimulateStuckJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:stuck:simulate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate stuck jobs detection for testing purpose.';

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected */
    public function handle(): void
    {
        $fakeStuckJobs = collect([
            ['failed_at' => Carbon::now()],
            ['failed_at' => Carbon::now()],
        ]);
        $notification = (new StuckJobsNotifier)->getNotification($fakeStuckJobs, true);
        (new StuckJobsNotifier)->getNotifiable()->notify($notification);
        $callback = (new StuckJobsNotifier)->getCallback();
        if ($callback) {
            $callback($fakeStuckJobs, true);
        }
    }
}
