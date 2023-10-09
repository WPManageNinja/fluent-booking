<?php

namespace FluentBooking\App\Services\Integrations\Calendars;


use FluentBooking\App\Models\Booking;

class RemoteCalendarsInit
{
    public function boot()
    {
        (new \FluentBooking\App\Services\Integrations\Calendars\Google\Bootstrap())->register();

        add_action('fluent_booking/after_booking_scheduled', [$this, 'checkForRemoteCalendarEventInsert'], 10, 2);
    }

    public function checkForRemoteCalendarEventInsert($booking, $slot)
    {
        $calendar = $slot->calendar;

        if (!$calendar) {
            return;
        }

        $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);
        if (!$config) {
            return; // no integration available
        }

        do_action('fluent_booking/create_remote_calendar_event_' . $config['driver'], $config, $booking, $slot);
    }
}
