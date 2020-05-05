<?php

namespace Okipa\LaravelStuckJobsNotifier;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected;

class Callback
{
    /**
     * Callback constructor.
     *
     * @param \Illuminate\Support\Collection $stuckJobs
     *
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected
     */
    public function __construct(Collection $stuckJobs)
    {
        $stuckJobsCount = $stuckJobs->count();
        throw new StuckJobsDetected($stuckJobsCount . ' stuck failed '
            . Str::plural('job', $stuckJobsCount) . ' detected');
    }
}
