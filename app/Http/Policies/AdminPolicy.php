<?php

namespace FluentBooking\App\Http\Policies;

use FluentBooking\Framework\Request\Request;
use FluentBooking\Framework\Foundation\Policy;

class AdminPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  \FluentBooking\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return current_user_can('manage_options');
    }

    /**
     * Check user permission for any method
     * @param  \FluentBooking\Framework\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return current_user_can('manage_options');
    }
}
