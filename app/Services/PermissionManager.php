<?php

namespace FluentBooking\App\Services;

class PermissionManager
{
    public static function hasAllCalendarAccess()
    {
        return apply_filters('fluent_calendar/has_all_calendar_access', current_user_can('manage_options'));
    }
}
