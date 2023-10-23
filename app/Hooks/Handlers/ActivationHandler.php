<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\Framework\Foundation\Application;
use FluentBooking\Database\DBMigrator;
use FluentBooking\Database\DBSeeder;

class ActivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($network_wide = false)
    {
        DBMigrator::run($network_wide);
        DBSeeder::run();

        $this->registerWpCron();
    }

    public function registerWpCron()
    {
        $fiveMinutesHook = 'fluent_booking_five_minutes_tasks';
        as_schedule_recurring_action(time(), (60 * 5), $fiveMinutesHook, [], 'fluent-booking', true);

        $hourlyHook = 'fluent_booking_hourly_tasks';
        if (!wp_next_scheduled($hourlyHook)) {
            wp_schedule_event(time(), 'hourly', $hourlyHook);
        }

        $dailyHook = 'fluent_booking/daily_tasks';
        if (!wp_next_scheduled($dailyHook)) {
            wp_schedule_event(time(), 'daily', $dailyHook);
        }
    }
}
