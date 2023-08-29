<?php

add_action('init', function () {

    if (defined('FLUENTFORM')) {
        (new \FluentCalendar\App\Services\Integrations\FluentForms\FluentFormInit())->init();
    }

});
