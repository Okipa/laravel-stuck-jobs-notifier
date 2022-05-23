<?php

namespace Okipa\LaravelStuckJobsNotifier\Test\Unit;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\Schema;
use Okipa\LaravelStuckJobsNotifier\Commands\SimulateStuckJobs;
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
use Okipa\LaravelStuckJobsNotifier\Test\TestCase;

class StuckJobsMonitoringTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NotificationFacade::fake();
    }

    /** @test */
    public function it_can_check_if_allowed_to_run_with_wrong_config_value(): void
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', 'test');
        $this->expectException(InvalidAllowedToRun::class);
        app(StuckJobsNotifier::class)->isAllowedToRun();
    }

    /** @test */
    public function it_can_check_if_allowed_to_run_with_boolean_value(): void
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', false);
        $allowedToRun = app(StuckJobsNotifier::class)->isAllowedToRun();
        self::assertFalse($allowedToRun);
    }

    /** @test */
    public function it_can_check_if_allowed_to_run_with_callable_value(): void
    {
        config()->set('stuck-jobs-notifier.allowed_to_run', fn() => true);
        $allowedToRun = app(StuckJobsNotifier::class)->isAllowedToRun();
        self::assertTrue($allowedToRun);
    }

    /** @test */
    public function it_can_fetch_failed_jobs_when_table_does_not_exist(): void
    {
        Schema::drop('failed_jobs');
        $this->expectException(InexistentFailedJobsTable::class);
        app(StuckJobsNotifier::class)->checkFailedJobsTableExists();
    }

    /** @test */
    public function it_cant_get_hours_limit_with_wrong_config_value(): void
    {
        config()->set('stuck-jobs-notifier.hours_limit', 'test');
        $this->expectException(InvalidHoursLimit::class);
        app(StuckJobsNotifier::class)->getHoursLimit();
    }

    /** @test */
    public function it_can_get_hours_limit_with_int_config_value(): void
    {
        config()->set('stuck-jobs-notifier.hours_limit', 5);
        $hoursLimit = app(StuckJobsNotifier::class)->getHoursLimit();
        self::assertEquals(5, $hoursLimit);
    }

    /** @test */
    public function it_can_get_stuck_failed_jobs(): void
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
        $stuckJobs = app(StuckJobsNotifier::class)->getStuckFailedJobs();
        $dateLimit = app(StuckJobsNotifier::class)->getDateLimit();
        foreach ($stuckJobs as $stuckJob) {
            self::assertTrue($dateLimit->greaterThanOrEqualTo($stuckJob->failed_at));
        }
    }

    /** @test */
    public function it_can_set_custom_notifiable(): void
    {
        config()->set('stuck-jobs-notifier.notifiable', AnotherNotifiable::class);
        $notifiable = app(StuckJobsNotifier::class)->getNotifiable();
        self::assertInstanceOf(AnotherNotifiable::class, $notifiable);
    }

    /** @test */
    public function it_can_set_custom_notification(): void
    {
        config()->set('stuck-jobs-notifier.notification', AnotherNotification::class);
        $notification = app(StuckJobsNotifier::class)->getNotification(collect());
        self::assertInstanceOf(AnotherNotification::class, $notification);
    }

    /** @test */
    public function it_can_set_custom_callback(): void
    {
        config()->set('stuck-jobs-notifier.callback', AnotherCallback::class);
        $callback = app(StuckJobsNotifier::class)->getCallback();
        self::assertInstanceOf(AnotherCallback::class, $callback);
    }

    /** @test */
    public function it_cant_send_notification_when_not_allowed_to_run(): void
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

    /** @test */
    public function it_can_send_notification_when_jobs_are_stuck(): void
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

    /** @test */
    public function it_can_trigger_callback_when_jobs_are_stuck(): void
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

    /** @test */
    public function it_can_send_default_processes_are_down_singular_notification_message(): void
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
        ]);
        $notification = app(StuckJobsNotifier::class)->getNotification($stuckJobs);
        $notifiable = app(StuckJobsNotifier::class)->getNotifiable();
        $notifiable->notify($notification);
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            static function ($notification, $channels) use ($date) {
                self::assertEquals(config('stuck-jobs-notifier.channels'), $channels);
                // Mail
                $mailData = $notification->toMail($channels)->toArray();
                self::assertEquals('error', $mailData['level']);
                self::assertEquals('[Laravel - testing] 1 job is stuck in queue', $mailData['subject']);
                self::assertEquals(
                    'We have detected that 1 job is stuck in the [Laravel - testing](http://localhost) queue '
                    . 'since the ' . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $mailData['introLines'][0]
                );
                self::assertEquals(
                    'Please check your stuck jobs connecting to your server and executing the '
                    . '"php artisan queue:failed" command.',
                    $mailData['introLines'][1]
                );
                // Slack
                $slackData = $notification->toSlack($channels);
                self::assertEquals('error', $slackData->level);
                self::assertEquals(
                    '⚠ `[Laravel - testing]` 1 job is stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $slackData->content
                );
                // Webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                self::assertEquals(
                    '⚠ `[Laravel - testing]` 1 job is stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $webhookData['data']['text']
                );

                return true;
            }
        );
    }

    /** @test */
    public function it_can_send_default_processes_are_down_plural_notification_message(): void
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
            ['failed_at' => $date->copy()->addHour()->toDateTimeString()],
        ]);
        $notification = app(StuckJobsNotifier::class)->getNotification($stuckJobs);
        $notifiable = app(StuckJobsNotifier::class)->getNotifiable();
        $notifiable->notify($notification);
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            static function ($notification, $channels) use ($date) {
                self::assertEquals(config('stuck-jobs-notifier.channels'), $channels);
                // Mail
                $mailData = $notification->toMail($channels)->toArray();
                self::assertEquals('error', $mailData['level']);
                self::assertEquals('[Laravel - testing] 2 jobs are stuck in queue', $mailData['subject']);
                self::assertEquals(
                    'We have detected that 2 jobs are stuck in the [Laravel - testing](http://localhost) queue '
                    . 'since the ' . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $mailData['introLines'][0]
                );
                self::assertEquals(
                    'Please check your stuck jobs connecting to your server and executing the '
                    . '"php artisan queue:failed" command.',
                    $mailData['introLines'][1]
                );
                // Slack
                $slackData = $notification->toSlack($channels);
                self::assertEquals('error', $slackData->level);
                self::assertEquals(
                    '⚠ `[Laravel - testing]` 2 jobs are stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $slackData->content
                );
                // Webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                self::assertEquals(
                    '⚠ `[Laravel - testing]` 2 jobs are stuck in the http://localhost queue since the '
                    . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.',
                    $webhookData['data']['text']
                );

                return true;
            }
        );
    }

    /** @test */
    public function it_can_send_default_down_processed_callback_exception_singular_message(): void
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
        ]);
        $callback = app(StuckJobsNotifier::class)->getCallback();
        $this->expectExceptionMessage('1 job is stuck in queue since the '
            . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.');
        $callback($stuckJobs);
    }

    /** @test */
    public function it_can_send_default_down_processed_callback_exception_plural_message(): void
    {
        $date = Carbon::now()->subHours(4);
        $stuckJobs = collect([
            ['failed_at' => $date->toDateTimeString()],
            ['failed_at' => $date->copy()->addHour()->toDateTimeString()],
        ]);
        $callback = app(StuckJobsNotifier::class)->getCallback();
        $this->expectExceptionMessage('2 jobs are stuck in queue since the '
            . $date->format('d/m/Y') . ' at ' . $date->format('H:i:s') . '.');
        $callback($stuckJobs);
    }

    /** @test */
    public function it_can_simulate_stuck_jobs_notification(): void
    {
        config()->set('stuck-jobs-notifier.callback', null);
        $this->artisan(SimulateStuckJobs::class);
        NotificationFacade::assertSentTo(
            new Notifiable(),
            JobsAreStuck::class,
            function ($notification, $channels) {
                // Mail
                $mailData = $notification->toMail($channels)->toArray();
                $this->assertStringContainsString('Notification test: ', $mailData['subject']);
                $this->assertStringContainsString('Notification test: ', $mailData['introLines'][0]);
                // Slack
                $slackData = $notification->toSlack($channels);
                $this->assertStringContainsString('Notification test: ', $slackData->content);
                // Webhook
                $webhookData = $notification->toWebhook($channels)->toArray();
                $this->assertStringContainsString('Notification test: ', $webhookData['data']['text']);

                return true;
            }
        );
    }

    /** @test */
    public function it_can_simulate_stuck_jobs_exception(): void
    {
        $this->expectExceptionMessage('Exception test: ');
        $this->artisan(SimulateStuckJobs::class);
    }
}
