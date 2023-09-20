<?php

add_action('init', function () {

    if (defined('FLUENTFORM')) {
        (new \FluentBooking\App\Services\Integrations\FluentForms\FluentFormInit())->init();
    }

    if (defined('FLUENTCRM')) {
        (new \FluentBooking\App\Services\Integrations\FluentCRM\FluentCrmInit());
    }
    
    (new \FluentBooking\App\Services\Integrations\GoogleCalendar\GoogleCalendar());
});
