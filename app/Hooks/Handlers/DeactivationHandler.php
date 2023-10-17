<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\Framework\Foundation\Application;

class DeactivationHandler
{
    protected $app = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        as_unschedule_action('fluent_booking_minute_tasks');
        wp_clear_scheduled_hook('fluent_booking_five_minutes_tasks');
        wp_clear_scheduled_hook('fluent_booking_hourly_tasks');
    }

}
