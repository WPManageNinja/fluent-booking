<?php

namespace FluentCalendar\App\Hooks\Handlers;

use FluentCalendar\Framework\Foundation\Application;

class DeactivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    public function handle()
    {
        wp_clear_scheduled_hook('fluent_calendar_minute_tasks');
        wp_clear_scheduled_hook('fluent_calendar_hourly_tasks');
    }

}
