<?php

namespace FluentBooking\App\Hooks\Handlers\CleanupHandlers;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\CalendarSlot;

class CalenderCleaner
{
    public function register()
    {
        add_action('fluent_booking/before_delete_calendar', [$this, 'handleBeforeDelete']);
    }

    public function handleBeforeDelete($calendar)
    {
        if (empty($calendar)) {
            return;
        }
        $calendarEvents = CalendarSlot::query()
            ->where('calendar_id', $calendar->id)->get();

        if ($calendarEvents->count()) {
            foreach ($calendarEvents as $event) {
                do_action('fluent_booking/before_delete_calendar_event', $event, $calendar);
                $event->delete();
                do_action('fluent_booking/after_delete_calendar_event', $event, $calendar);
            }
        }

    }
}