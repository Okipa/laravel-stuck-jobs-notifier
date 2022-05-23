<?php

namespace Okipa\LaravelStuckJobsNotifier\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;

class SimulateStuckJobs extends Command
{
    /** @var string */
    protected $signature = 'queue:stuck:simulate';

    /** @var string */
    protected $description = 'Simulate stuck jobs detection for testing purpose.';

    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected */
    public function handle(): void
    {
        $fakeStuckJobs = collect([
            ['failed_at' => Carbon::now()],
            ['failed_at' => Carbon::now()],
        ]);
        $notification = app(StuckJobsNotifier::class)->getNotification($fakeStuckJobs, true);
        app(StuckJobsNotifier::class)->getNotifiable()->notify($notification);
        $callback = app(StuckJobsNotifier::class)->getCallback();
        if ($callback) {
            $callback($fakeStuckJobs, true);
        }
    }
}
