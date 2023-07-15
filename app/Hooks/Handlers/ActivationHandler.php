<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\Framework\Foundation\Application;
use FluentCalendar\Database\DBMigrator;
use FluentCalendar\Database\DBSeeder;

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
        $dailyHook = 'fluent_calendar_hourly_tasks';
        if (!wp_next_scheduled($dailyHook)) {
            wp_schedule_event(time(), 'hourly', $dailyHook);
        }
    }
}
