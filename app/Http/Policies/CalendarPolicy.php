<?php

namespace FluentCalendar\App\Http\Policies;

use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Foundation\Policy;

class CalendarPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentCalendar\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return apply_filters('fluent_calendar/verify_calendar_api', current_user_can('manage_options'), $request);
    }
}
