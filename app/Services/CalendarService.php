<?php

namespace FluentBooking\App\Services;

use FluentBooking\App\Models\CalendarSlot;

class CalendarService
{
    public static function getSlotOptions($userId)
    {

        $calendarSlots = CalendarSlot::select(['id', 'title'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $options = [];
        foreach ($calendarSlots as $slot) {
            $options[] = [
                'id'    => $slot->id,
                'label' => $slot->title,
            ];
        }
        return apply_filters('fluent_booking/calendar_event_options', $options, $userId);
    }
}
