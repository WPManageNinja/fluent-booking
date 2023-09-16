<?php

namespace FluentBooking\App\Http\Policies;

use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Foundation\Policy;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentBooking\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return !!get_current_user_id();
    }
}
