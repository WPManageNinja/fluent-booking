<?php

namespace FluentCalendar\App\Http\Policies;

use FluentCalendar\Framework\Request\Request;
use FluentCalendar\Framework\Foundation\Policy;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentCalendar\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return !!get_current_user_id();
    }
}
