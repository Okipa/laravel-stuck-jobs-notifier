<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Dummy;

use Mockery\Exception;

class Callback
{
    public function __construct()
    {
        throw new Exception('test');
    }
}
