<?php

namespace FluentBooking\App\Services\Integrations\Calendars;


use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;

class RemoteCalendarsInit
{
    public function boot()
    {
        (new \FluentBooking\App\Services\Integrations\Calendars\Google\Bootstrap())->register();

        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'checkForRemoteCalendarEventInsert'], 10, 2);

        add_action('fluent_booking/before_patch_booking_schedule', [$this, 'checkForRemoteCalendarEventUpdate'], 10, 2);

        add_action('fluent_booking/after_disconnect_remote_calendar', function ($metaId, $calendar) {
            $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);
            if (!$config) {
                return; // no integration available
            }

            $meta = Meta::where('id', $config['db_id'])->first();
            if (!$meta) {
                RemoteCalendarHelper::updateUserRemoteCreatableCalendarSettings($calendar->user_id, []);
            }

        }, 10, 2);
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

    public function checkForRemoteCalendarEventUpdate($booking, $data)
    {

        $calendar = Calendar::where('id', $booking->calendar_id)->first();

        if (!$calendar) {
            return;
        }

        $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);

        if (!$config) {
            return;
        }

        do_action('fluent_booking/update_remote_calendar_event_' . $config['driver'], $config, $booking, $data, $calendar);
    }
}
