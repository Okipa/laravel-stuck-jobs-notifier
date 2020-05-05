<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Unit;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification;
use Okipa\LaravelStuckJobsNotifier\Notifiable;
use Okipa\LaravelStuckJobsNotifier\Notification;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;
use Okipa\LaravelStuckJobsNotifier\Test\BootstrapComponentsTestCase;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\AnotherNotifiable;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\AnotherNotification;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\Callback;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\WrongNotification;

class StuckJobsMonitoringTest extends BootstrapComponentsTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        NotificationFacade::fake();
    }

    public function testAllowedToRunWithWrongValue()
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', 'test');
        $this->expectException(InvalidAllowedToRun::class);
        (new StuckJobsNotifier)->isAllowedToRun();
    }

    public function testAllowedToRunWithBoolean()
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', false);
        $allowedToRun = (new StuckJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, false);
    }

    public function testAllowedToRunWithCallable()
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', function () {
            return true;
        });
        $allowedToRun = (new StuckJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, true);
    }

    public function testCustomCallback()
    {
        config()->set('stuck-jobs-notifier.callback', Callback::class);
        $this->expectException(Exception::class);
        (new StuckJobsNotifier)->executeCallback(collect(['test']));
    }

    public function testFailedJobTableDoesNotExists()
    {
        Schema::drop('failed_jobs');
        $this->expectException(InexistentFailedJobsTable::class);
        (new StuckJobsNotifier)->checkFailedJobsTableExists();
    }

    public function testSetDaysLimitWithWrongValue()
    {
        config()->set('stuck-jobs-notifier.hours_limit', 'test');
        $this->expectException(InvalidHoursLimit::class);
        (new StuckJobsNotifier)->getHoursLimit();
    }

    public function testSetDaysLimitWithInt()
    {
        config()->set('stuck-jobs-notifier.hours_limit', 5);
        $hoursLimit = (new StuckJobsNotifier)->getHoursLimit();
        $this->assertEquals(5, $hoursLimit);
    }

    public function testGetStuckFailedJobs()
    {
        $failedAtDates = [
            Carbon::now()->subHours(6)->startOfHour(),
            Carbon::now()->subHours(6)->minutes(30)->seconds(0),
            Carbon::now()->subHours(6)->endOfHour(),
            Carbon::now()->subHours(5)->startOfHour(),
            Carbon::now()->subHours(5)->minutes(30)->seconds(0),
            Carbon::now()->subHours(5)->endOfHour(),
            Carbon::now()->subHours(4)->startOfHour(),
            Carbon::now()->subHours(4)->minutes(30)->seconds(0),
            Carbon::now()->subHours(4)->endOfHour(),
        ];
        foreach ($failedAtDates as $failedAt) {
            DB::table('failed_jobs')->insert([
                'connection' => 'whatever',
                'queue' => 'default',
                'payload' => 'test',
                'exception' => 'test',
                'failed_at' => $failedAt,
            ]);
        }
        config()->set('stuck-jobs-notifier.hours_limit', 5);
        $stuckJobs = (new StuckJobsNotifier)->getStuckFailedJobs();
        $dateLimit = (new StuckJobsNotifier)->getDateLimit();
        foreach ($stuckJobs as $stuckJob) {
            $this->assertTrue($dateLimit->greaterThanOrEqualTo($stuckJob->failed_at));
        }
    }

    public function testGetWrongNotification()
    {
        config()->set('stuck-jobs-notifier.notification', WrongNotification::class);
        $this->expectException(InvalidNotification::class);
        (new StuckJobsNotifier)->getNotification(collect([]));
    }

    public function testGetDefaultNotification()
    {
        $notification = (new StuckJobsNotifier)->getNotification(collect([]));
        $this->assertInstanceOf(\Illuminate\Notifications\Notification::class, $notification);
    }

    public function testGetCustomNotification()
    {
        config()->set('stuck-jobs-notifier.notification', AnotherNotification::class);
        $notification = (new StuckJobsNotifier)->getNotification(collect([]));
        $this->assertInstanceOf(\Illuminate\Notifications\Notification::class, $notification);
    }

    public function testNotificationIsSentWithCustomNotifiable()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(4),
        ]);
        config()->set('stuck-jobs-notifier.callback', null);
        config()->set('stuck-jobs-notifier.hours_limit', 3);
        config()->set('stuck-jobs-notifier.notifiable', AnotherNotifiable::class);
        $this->artisan('queue:stuck:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new AnotherNotifiable(), Notification::class);
    }

    public function testNotificationIsSentWithCustomNotification()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(4),
        ]);
        config()->set('stuck-jobs-notifier.callback', null);
        config()->set('stuck-jobs-notifier.hours_limit', 3);
        config()->set('stuck-jobs-notifier.notification', AnotherNotification::class);
        $this->artisan('queue:stuck:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), AnotherNotification::class);
    }

    public function testNotificationIsNotSentWhenNotAllowedTo()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subHours(4),
        ]);
        config()->set('stuck-jobs-notifier.hours_limit', 3);
        config()->set('stuck-jobs-notifier.allowed_to_run', false);
        $this->artisan('queue:stuck:notify')->assertExitCode(0);
        NotificationFacade::assertNotSentTo(new Notifiable(), Notification::class);
    }
}
