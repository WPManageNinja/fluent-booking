<?php

add_action('init', function () {

    if (defined('FLUENTFORM')) {
        (new \FluentBooking\App\Services\Integrations\FluentForms\FluentFormInit())->init();
    }

    if (defined('FLUENTCRM')) {
        (new \FluentBooking\App\Services\Integrations\FluentCRM\FluentCrmInit());
    }

   // (new \FluentBooking\App\Services\Integrations\GoogleCalendar\GoogleCalendar());

});


/*
 * Remote calendars
 */
(new \FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarsInit())->boot();
(new \FluentBooking\App\Services\Integrations\Twilio\Bootstrap())->register();
(new \FluentBooking\App\Services\Integrations\ZoomMeeting\Bootstrap())->register();
