<?php

namespace Okipa\LaravelStuckJobsNotifier\Callbacks;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected;

class OnStuckJobs
{
    /** @throws \Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected */
    public function __invoke(Collection $stuckJobs, bool $isTesting = false)
    {
        $stuckJobsCount = $stuckJobs->count();
        $stuckSince = Carbon::parse($stuckJobs->min('failed_at'));
        // Triggers an exception to make your monitoring tool (Sentry, ...) aware of the problem.
        throw new StuckJobsDetected(($isTesting ? __('Exception test:') . ' ' : '')
            . trans_choice(
                '{1}:count job is stuck in queue since the :day at :hour.'
                . '|[2,*]:count jobs are stuck in queue since the :day at :hour.',
                $stuckJobsCount,
                [
                    'count' => $stuckJobsCount,
                    'day' => $stuckSince->format('d/m/Y'),
                    'hour' => $stuckSince->format('H:i:s'),
                ]
            ));
    }
}
