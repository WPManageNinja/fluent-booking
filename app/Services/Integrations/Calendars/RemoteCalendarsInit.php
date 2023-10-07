<?php

namespace FluentBooking\App\Services\Integrations\Calendars;


class RemoteCalendarsInit
{
    public function boot()
    {
        (new \FluentBooking\App\Services\Integrations\Calendars\Google\Bootstrap())->register();
    }
}
