<?php

namespace FluentBooking\App\Services;

class PermissionManager
{
    public static function hasAllCalendarAccess()
    {
        return apply_filters('fluent_booking/has_all_calendar_access', current_user_can('manage_options'));
    }

    public static function hasCalendarAccess($calendar)
    {
        return current_user_can('manage_options') || $calendar->user_id === get_current_user_id();
    }
}
