<?php

namespace Okipa\LaravelStuckJobsNotifier\Callbacks;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected;

class OnStuckJobs
{
    /**
     * @param \Illuminate\Support\Collection $stuckJobs
     *
     * @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected
     */
    public function __invoke(Collection $stuckJobs)
    {
        $stuckJobsCount = $stuckJobs->count();
        throw new StuckJobsDetected($stuckJobsCount . ' stuck failed '
            . Str::plural('job', $stuckJobsCount) . ' detected');
    }
}
