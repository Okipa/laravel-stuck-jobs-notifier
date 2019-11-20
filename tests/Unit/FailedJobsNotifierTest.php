<?php

namespace Okipa\LaravelFailedJobsNotifier\Test\Unit;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidDaysLimit;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidNotification;
use Okipa\LaravelFailedJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelFailedJobsNotifier\FailedJobsNotifier;
use Okipa\LaravelFailedJobsNotifier\Notifiable;
use Okipa\LaravelFailedJobsNotifier\Notification;
use Okipa\LaravelFailedJobsNotifier\Test\BootstrapComponentsTestCase;
use Okipa\LaravelFailedJobsNotifier\Test\Dummy\AnotherNotifiable;
use Okipa\LaravelFailedJobsNotifier\Test\Dummy\AnotherNotification;
use Okipa\LaravelFailedJobsNotifier\Test\Dummy\WrongNotification;

class FailedJobMonitorTest extends BootstrapComponentsTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        NotificationFacade::fake();
    }

    public function testSetAllowedToRunWithWrongValue()
    {
        config()->set('failed-jobs-notifier.allowedToRun', 'test');
        $this->expectException(InvalidAllowedToRun::class);
        (new FailedJobsNotifier)->isAllowedToRun();
    }

    public function testSetAllowedToRunWithBoolean()
    {
        config()->set('failed-jobs-notifier.allowedToRun', false);
        $allowedToRun = (new FailedJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, false);
    }

    public function testSetAllowedToRunWithCallable()
    {
        config()->set('failed-jobs-notifier.allowedToRun', function () {
            return true;
        });
        $allowedToRun = (new FailedJobsNotifier)->isAllowedToRun();
        $this->assertEquals($allowedToRun, true);
    }

    public function testFailedJobTableDoesNotExists()
    {
        Schema::drop('failed_jobs');
        $this->expectException(InexistentFailedJobsTable::class);
        (new FailedJobsNotifier)->checkFailedJobsTableExists();
    }

    public function testSetDaysLimitWithWrongValue()
    {
        config()->set('failed-jobs-notifier.daysLimit', 'test');
        $this->expectException(InvalidDaysLimit::class);
        (new FailedJobsNotifier)->getDaysLimit();
    }

    public function testSetDaysLimitWithInt()
    {
        config()->set('failed-jobs-notifier.daysLimit', 5);
        $daysLimit = (new FailedJobsNotifier)->getDaysLimit();
        $this->assertEquals(5, $daysLimit);
    }

    public function testGetStuckFailedJobs()
    {
        $now = Carbon::now();
        $failedAtDates = [
            $now->copy()->subDays(6)->startOfDay(),
            $now->copy()->subDays(6)->midDay(),
            $now->copy()->subDays(6)->endOfDay(),
            $now->copy()->subDays(5)->startOfDay(),
            $now->copy()->subDays(5)->midDay(),
            $now->copy()->subDays(5)->endOfDay(),
            $now->copy()->subDays(4)->startOfDay(),
            $now->copy()->subDays(4)->midDay(),
            $now->copy()->subDays(4)->endOfDay(),
        ];
        foreach ($failedAtDates as $failedAt) {
            DB::table('failed_jobs')->insert([
                'connection' => 'whatever',
                'queue'      => 'default',
                'payload'    => 'test',
                'exception'  => 'test',
                'failed_at'  => $failedAt,
            ]);
        }
        config()->set('failed-jobs-notifier.daysLimit', 5);
        $stuckFailedJobs = (new FailedJobsNotifier)->getStuckFailedJobs();
        $dateLimit = Carbon::now()->subDays(5);
        foreach ($stuckFailedJobs as $stuckFailedJob) {
            $this->assertTrue($dateLimit->greaterThanOrEqualTo($stuckFailedJob->failed_at));
        }
    }

    public function testGetWrongNotification()
    {
        config()->set('failed-jobs-notifier.notification', WrongNotification::class);
        $this->expectException(InvalidNotification::class);
        (new FailedJobsNotifier)->getNotification(collect([]));
    }

    public function testGetDefaultNotification()
    {
        $notification = (new FailedJobsNotifier)->getNotification(collect([]));
        $this->assertInstanceOf(\Illuminate\Notifications\Notification::class, $notification);
    }

    public function testGetCustomNotification()
    {
        config()->set('failed-jobs-notifier.notification', AnotherNotification::class);
        $notification = (new FailedJobsNotifier)->getNotification(collect([]));
        $this->assertInstanceOf(\Illuminate\Notifications\Notification::class, $notification);
    }

    public function testNotificationIsSentWithCustomNotifiable()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue'      => 'default',
            'payload'    => 'test',
            'exception'  => 'test',
            'failed_at'  => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.notifiable', AnotherNotifiable::class);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new AnotherNotifiable(), Notification::class);
    }

    public function testNotificationIsSentWithCustomNotification()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue'      => 'default',
            'payload'    => 'test',
            'exception'  => 'test',
            'failed_at'  => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.notification', AnotherNotification::class);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), AnotherNotification::class);
    }

    public function testNotificationIsNotSentWhenNotAllowedTo()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue'      => 'default',
            'payload'    => 'test',
            'exception'  => 'test',
            'failed_at'  => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.allowedToRun', false);
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertNotSentTo(new Notifiable(), Notification::class);
    }

    public function testNotificationIsNotSentWhenAllowedTo()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue'      => 'default',
            'payload'    => 'test',
            'exception'  => 'test',
            'failed_at'  => Carbon::now()->subDays(2),
        ]);
        config()->set('failed-jobs-notifier.allowedToRun', function () {
            return true;
        });
        $this->artisan('queue:failed:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), Notification::class);
    }
}
