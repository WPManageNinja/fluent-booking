<?php

use FluentCalendar\Framework\Foundation\Application;
use FluentCalendar\App\Hooks\Handlers\ActivationHandler;
use FluentCalendar\App\Hooks\Handlers\DeactivationHandler;

return function($file) {

    $app = new Application($file);

    register_activation_hook($file, function() use ($app) {
        ($app->make(ActivationHandler::class))->handle();
    });

    register_deactivation_hook($file, function() use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    add_action('plugins_loaded', function() use ($app) {
        do_action('fluentcalendar_loaded', $app);
    });
};
