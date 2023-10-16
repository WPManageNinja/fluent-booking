<?php

namespace FluentBooking\App\Hooks\Handlers\CleanupHandlers;

use FluentBooking\App\Models\Booking;

class CalenderEventCleaner
{
    public function register()
    {
        add_action('fluent_booking/before_delete_calendar_event', [$this, 'handleBeforeDelete'], 10, 2);
    }

    public function handleBeforeDelete($calendarEvent, $calendar)
    {
        if (empty($calendarEvent)) {
            return;
        }

        $booking = Booking::query()->where('event_id', $calendarEvent->id)
            ->when($calendar, function ($query, $booking) {
                $query->where('calendar_id', $booking->id);
            })
            ->first();

        if ($booking) {
            do_action('fluent_booking/before_delete_booking', $booking);
            $booking->delete();
            do_action('fluent_booking/after_delete_booking', $booking);
        }


    }
}