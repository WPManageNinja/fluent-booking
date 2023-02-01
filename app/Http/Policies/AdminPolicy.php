<?php

namespace FluentCalendar\App\Http\Policies;

use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Foundation\Policy;

class AdminPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentCalendar\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return true;
        return current_user_can('manage_options');
    }

    /**
     * Check user permission for any method
     * @param  \FluentCalendar\Framework\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return current_user_can('manage_options');
    }
}
