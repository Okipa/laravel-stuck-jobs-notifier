<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Unit;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InexistentFailedJobsTable;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidAllowedToRun;
use Okipa\LaravelStuckJobsNotifier\Exceptions\InvalidHoursLimit;
use Okipa\LaravelStuckJobsNotifier\Exceptions\StuckJobsDetected;
use Okipa\LaravelStuckJobsNotifier\Notifiable;
use Okipa\LaravelStuckJobsNotifier\Notifications\JobsAreStuck;
use Okipa\LaravelStuckJobsNotifier\StuckJobsNotifier;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\AnotherNotifiable;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\Callbacks\AnotherCallback;
use Okipa\LaravelStuckJobsNotifier\Test\Dummy\Notifications\AnotherNotification;
use Okipa\LaravelStuckJobsNotifier\Test\FailedJobsNotifierTestCase;

class StuckJobsMonitoringTest extends FailedJobsNotifierTestCase
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

    public function testSetCustomNotifiable()
    {
        config()->set('stuck-jobs-notifier.notifiable', AnotherNotifiable::class);
        $notifiable = (new StuckJobsNotifier)->getNotifiable();
        $this->assertInstanceOf(AnotherNotifiable::class, $notifiable);
    }

    public function testSetCustomNotification()
    {
        config()->set('stuck-jobs-notifier.notification', AnotherNotification::class);
        $notification = (new StuckJobsNotifier)->getNotification(collect());
        $this->assertInstanceOf(AnotherNotification::class, $notification);
    }

    public function testSetCustomCallback()
    {
        config()->set('stuck-jobs-notifier.callback', AnotherCallback::class);
        $callback = (new StuckJobsNotifier)->getCallback();
        $this->assertInstanceOf(AnotherCallback::class, $callback);
    }

    public function setNothingHappensWhenNotAllowed()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subDays(4),
        ]);
        config()->set('stuck-jobs-notifier.hours_limit', 3);
        config()->set('stuck-jobs-notifier.allowed_to_run', false);
        NotificationFacade::assertNothingSent();
    }

    public function testNotificationIsSentWhenJobsAreStuck()
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
        $this->artisan('queue:stuck:notify')->assertExitCode(0);
        NotificationFacade::assertSentTo(new Notifiable(), JobsAreStuck::class);
    }

    public function testCallbackIsTriggeredWhenHobsAreStuck()
    {
        DB::table('failed_jobs')->insert([
            'connection' => 'whatever',
            'queue' => 'default',
            'payload' => 'test',
            'exception' => 'test',
            'failed_at' => Carbon::now()->subHours(4),
        ]);
        config()->set('stuck-jobs-notifier.hours_limit', 3);
        $this->expectException(StuckJobsDetected::class);
        $this->artisan('queue:stuck:notify')->assertExitCode(0);
    }

    public function testDefaultProcessesAreDownNotificationSingularMessage()
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
        ]);
        $notification = (new StuckJobsNotifier)->getNotification($stuckJobs);
        $notifiable = (new StuckJobsNotifier)->getNotifiable();
        $notifiable->notify($notification);
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            function ($notification, $channels) use ($date) {
                $this->assertEquals(config('stuck-jobs-notifier.channels'), $channels);
                // mail
                $mailData = $notification->toMail($channels)->toArray();
                $this->assertEquals('error', $mailData['level']);
                $this->assertEquals('[Laravel - testing] 1 job is stuck in queue', $mailData['subject']);
                $this->assertEquals(
                    'We have detected that 1 job is stuck in the [Laravel - testing](http://localhost) queue '
                    . 'since the ' . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $mailData['introLines'][0]
                );
                $this->assertEquals(
                    'Please check your stuck jobs connecting to your server and executing the '
                    . '"php artisan queue:failed" command.',
                    $mailData['introLines'][1]
                );
                // slack
                $slackData = $notification->toSlack($channels);
                $this->assertEquals('error', $slackData->level);
                $this->assertEquals(
                    '⚠ `[Laravel - testing]` 1 job is stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $slackData->content
                );
                // webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                $this->assertEquals(
                    '⚠ `[Laravel - testing]` 1 job is stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $webhookData['data']['text']
                );

                return true;
            }
        );
    }

    public function testDefaultProcessesAreDownNotificationPluralMessage()
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
            ['failed_at' => $date->copy()->addHour()->toDateTimeString()],
        ]);
        $notification = (new StuckJobsNotifier)->getNotification($stuckJobs);
        $notifiable = (new StuckJobsNotifier)->getNotifiable();
        $notifiable->notify($notification);
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            function ($notification, $channels) use ($date) {
                $this->assertEquals(config('stuck-jobs-notifier.channels'), $channels);
                // mail
                $mailData = $notification->toMail($channels)->toArray();
                $this->assertEquals('error', $mailData['level']);
                $this->assertEquals('[Laravel - testing] 2 jobs are stuck in queue', $mailData['subject']);
                $this->assertEquals(
                    'We have detected that 2 jobs are stuck in the [Laravel - testing](http://localhost) queue '
                    . 'since the ' . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $mailData['introLines'][0]
                );
                $this->assertEquals(
                    'Please check your stuck jobs connecting to your server and executing the '
                    . '"php artisan queue:failed" command.',
                    $mailData['introLines'][1]
                );
                // slack
                $slackData = $notification->toSlack($channels);
                $this->assertEquals('error', $slackData->level);
                $this->assertEquals(
                    '⚠ `[Laravel - testing]` 2 jobs are stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $slackData->content
                );
                // webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                $this->assertEquals(
                    '⚠ `[Laravel - testing]` 2 jobs are stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $webhookData['data']['text']
                );

                return true;
            }
        );
    }

    public function testDefaultDownProcessesCallbackExceptionSingularMessage()
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
        ]);
        $callback = (new StuckJobsNotifier)->getCallback();
        $this->expectExceptionMessage('1 job is stuck in queue since the '
            . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.');
        $callback($stuckJobs);
    }

    public function testDefaultDownProcessesCallbackExceptionPluralMessage()
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
            ['failed_at' => $date->copy()->addHour()->toDateTimeString()],
        ]);
        $callback = (new StuckJobsNotifier)->getCallback();
        $this->expectExceptionMessage('2 jobs are stuck in queue since the '
            . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.');
        $callback($stuckJobs);
    }

    public function testSimulationNotification()
    {
        config()->set('stuck-jobs-notifier.callback', null);
        $this->artisan('queue:stuck:simulate');
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            function ($notification, $channels) {
                $this->assertEquals(config('stuck-jobs-notifier.channels'), $channels);
                // mail
                $mailData = $notification->toMail($channels)->toArray();
                $this->assertStringContainsString('Notification test: ', $mailData['subject']);
                $this->assertStringContainsString('Notification test: ', $mailData['introLines'][0]);
                // slack
                $slackData = $notification->toSlack($channels);
                $this->assertStringContainsString('Notification test: ', $slackData->content);
                // webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                $this->assertStringContainsString('Notification test: ', $webhookData['data']['text']);

                return true;
            }
        );
    }

    public function testSimulationCallback()
    {
        $this->expectExceptionMessage('Exception test: ');
        $this->artisan('queue:stuck:simulate');
    }
}
