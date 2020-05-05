<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Unit;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidNotification;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;
use Okipa\LaravelStuckJobsNotifier\Notifiable;
use Okipa\LaravelStuckJobsNotifier\Notification;
use Okipa\LaravelStuckJobsNotifier\Test\BootstrapComponentsTestCase;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\AnotherNotifiable;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\AnotherNotification;
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
        config()->set('failed-jobs-notifier.allowedToRun', 'test');
        $this->expectException(InvalidAllowedToRun::class);
        (new StuckJobsNotifier)->isAllowedToRun();
    }

    public function testAllowedToRunWithBoolean()
    {
        config()->set('failed-jobs-notifier.allowedToRun', false);
        $allowedToRun = (new StuckJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, false);
    }

    public function testAllowedToRunWithCallable()
    {
        config()->set('failed-jobs-notifier.allowedToRun', function () {
            return true;
        });
        $allowedToRun = (new StuckJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, true);
    }

    public function testFailedJobTableDoesNotExists()
    {
        Schema::drop('failed_jobs');
        $this->expectException(InexistentFailedJobsTable::class);
        (new StuckJobsNotifier)->checkFailedJobsTableExists();
    }

    public function testSetDaysLimitWithWrongValue()
    {
        config()->set('failed-jobs-notifier.hoursLimit', 'test');
        $this->expectException(InvalidHoursLimit::class);
        (new StuckJobsNotifier)->getDaysLimit();
    }

    public function testSetDaysLimitWithInt()
    {
        config()->set('failed-jobs-notifier.hoursLimit', 5);
        $hoursLimit = (new StuckJobsNotifier)->getDaysLimit();
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
        config()->set('failed-jobs-notifier.hoursLimit', 5);
        $stuckJobs = (new StuckJobsNotifier)->getStuckFailedJobs();
        $this->assertCount(4, $stuckJobs);
    }

    public function testGetWrongNotification()
    {
        config()->set('failed-jobs-notifier.notification', WrongNotification::class);
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
        config()->set('failed-jobs-notifier.notification', AnotherNotification::class);
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
            'failed_at' => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.notifiable', AnotherNotifiable::class);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new AnotherNotifiable(), Notification::class);
    }

    public function testNotificationIsSentWithCustomNotification()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.notification', AnotherNotification::class);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), AnotherNotification::class);
    }

    public function testNotificationIsNotSentWhenNotAllowedTo()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.allowedToRun', false);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertNotSentTo(new Notifiable(), Notification::class);
    }

    public function testNotificationIsNotSentWhenAllowedTo()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.allowedToRun', function () {
            return true;
        });
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), Notification::class);
    }
}
