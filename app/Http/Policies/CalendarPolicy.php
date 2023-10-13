<?php

namespace FluentBooking\App\Http\Policies;

use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Foundation\Policy;

class CalendarPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentBooking\Framework\Request\Request $request
     * @return bool
     */
    public function verifyRequest(Request $request)
    {
        return true;
        return apply_filters('fluent_booking/verify_calendar_api', current_user_can('manage_options'), $request);
    }
}
