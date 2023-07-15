<?php

namespace FluentCalendar\app\Hooks\Handlers;

use FluentCalendar\App\Models\BookingActivity;
use FluentCalendar\Framework\Support\Arr;

class LogHandler
{
    public function register()
    {
        add_action('fluent_calendar/log_booking_note', [$this, 'logBookingActivity'], 10);
    }

    public function logBookingActivity($data)
    {
        $logData = array_filter(Arr::only($data, [
            'status',
            'type',
            'title',
            'description',
            'booking_id'
        ]));

        if (!$logData || empty($logData['booking_id'])) {
            return;
        }

        BookingActivity::create($logData);
    }

}
