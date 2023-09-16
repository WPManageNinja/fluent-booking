<?php

add_action('init', function () {

    if (defined('FLUENTFORM')) {
        (new \FluentBooking\App\Services\Integrations\FluentForms\FluentFormInit())->init();
    }

    (new \FluentBooking\App\Services\Integrations\GoogleCalendar\GoogleCalendar());
});
