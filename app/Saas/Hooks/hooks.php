<?php

(new \FluentCalendar\App\Saas\Hooks\Handlers\SaasHandler)->register();

add_filter('fluent_calendar/admin_base_url', function($url) {
    return site_url('calendar/#/');
});
