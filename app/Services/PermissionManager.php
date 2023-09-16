<?php

namespace FluentBooking\App\Services;

class PermissionManager
{
    public static function hasAllCalendarAccess()
    {
        return apply_filters('fluent_booking/has_all_calendar_access', current_user_can('manage_options'));
    }
}
