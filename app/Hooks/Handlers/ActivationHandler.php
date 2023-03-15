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
        add_filter('cron_schedules', function ($schedules) {
            $schedules['fluent_cal_every_minute'] = array(
                'interval' => 60,
                'display'  => esc_html__('Every Minute (Fluent Calendar)', 'fluent-Calendar'),
            );
            return $schedules;
        }, 10, 1);

        $hookName = 'fluent_calendar_minute_tasks';
        if (!wp_next_scheduled($hookName)) {
            wp_schedule_event(time(), 'fluent_cal_every_minute', $hookName);
        }

        $dailyHook = 'fluent_calendar_hourly_tasks';
        if (!wp_next_scheduled($dailyHook)) {
            wp_schedule_event(time(), 'hourly', $dailyHook);
        }

    }
}
