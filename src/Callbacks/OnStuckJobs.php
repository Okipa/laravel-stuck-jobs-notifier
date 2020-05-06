<?php

namespace Okipa\LaravelStuckJobsNotifier\Callbacks;

use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        // triggers an exception to make your monitoring tool (Sentry, ...) aware of the problem.
        throw new StuckJobsDetected(trans_choice(
            '{1}:count job is stuck in queue since :date.|[2,*]:count jobs are stuck in queue since :date.',
            $stuckJobsCount,
            [
                'count' => $stuckJobsCount,
                'date' => Carbon::parse($stuckJobs->min('failed_at'))->format('d/m/Y - H:i:s'),
            ]
        ));
    }
}
