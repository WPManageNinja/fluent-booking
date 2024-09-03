<?php

namespace FluentBooking\App\Services;

class CalendarEventService
{
    public static function processEvent($calendarEvent)
    {
        $calendarEvent->payment_html = $calendarEvent->getPaymentHtml();

        $calendarEvent->public_url = $calendarEvent->getPublicUrl();

        $calendarEvent->durations = $calendarEvent->getAvailableDurations();

        $calendarEvent->description = $calendarEvent->getDescription();

        $calendarEvent->short_description = Helper::excerpt($calendarEvent->description);

        $calendarEvent->locations = $calendarEvent->defaultLocationHtml();

        do_action_ref_array('fluent_booking/processed_event', [&$calendarEvent]);

        return $calendarEvent;
    }
}
