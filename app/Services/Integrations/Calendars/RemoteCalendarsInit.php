<?php

namespace FluentBooking\App\Services\Integrations\Calendars;


use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\Framework\Support\Arr;

class RemoteCalendarsInit
{
    public function boot()
    {
        (new \FluentBooking\App\Services\Integrations\Calendars\Google\Bootstrap())->register();

        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'checkForRemoteCalendarEventInsert'], 11, 2);

        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'checkForRemoteCalendarEventCancel'], 10, 1);

        add_action('fluent_booking/after_booking_rescheduled', [$this, 'checkForRemoteCalendarEventReschedule'], 10, 1);

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

        $bookingExist = Booking::where('group_id', $booking->group_id)->count();

        if ($bookingExist > 1) {
            do_action('fluent_booking/update_attendees_remote_calendar_event_' . $config['driver'], $config, $booking, 'add');
            return;
        }
        
        do_action('fluent_booking/create_remote_calendar_event_' . $config['driver'], $config, $booking, $slot);
    }

    public function checkForRemoteCalendarEventCancel($booking)
    {
        if ('cancelled' != Arr::get($booking, 'status')) {
            return false;
        }

        $calendar = Calendar::where('id', $booking->calendar_id)->first();

        if (!$calendar) {
            return;
        }

        $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);

        if (!$config) {
            return;
        }

        $bookingExist = Booking::where('group_id', $booking->group_id)->count();

        if ($bookingExist > 1) {
            do_action('fluent_booking/update_attendees_remote_calendar_event_' . $config['driver'], $config, $booking, 'remove');
            return;
        }

        $data['status'] = 'cancelled';
        do_action('fluent_booking/update_remote_calendar_event_' . $config['driver'], $config, $calendar, $booking, $data);

    }

    public function checkForRemoteCalendarEventReschedule($updatedBooking)
    {
        $calendar = Calendar::where('id', $updatedBooking->calendar_id)->first();

        if (!$calendar) {
            return;
        }

        $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);

        if (!$config) {
            return;
        }

        $data = [
            'start' => [
                'dateTime' => date('Y-m-d\TH:i:s\Z', strtotime($updatedBooking->start_time))
            ],
            'end'   => [
                'dateTime' => date('Y-m-d\TH:i:s\Z', strtotime($updatedBooking->end_time))
            ],
        ];

        do_action('fluent_booking/update_remote_calendar_event_' . $config['driver'], $config, $calendar, $updatedBooking, $data);

        $bookingExist = Booking::where('group_id', $updatedBooking->group_id)->count();

        if ($bookingExist > 1) {
            do_action('fluent_booking/update_attendees_remote_calendar_event_' . $config['driver'], $config, $updatedBooking, 'remove');
        }
    }
}
