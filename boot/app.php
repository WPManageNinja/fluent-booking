<?php

use FluentCalendar\Framework\Foundation\Application;
use FluentCalendar\App\Hooks\Handlers\ActivationHandler;
use FluentCalendar\App\Hooks\Handlers\DeactivationHandler;

return function ($file) {

    $app = new Application($file);

    register_activation_hook($file, function () use ($app) {
        ($app->make(ActivationHandler::class))->handle();
    });

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    require_once( FLUENT_CALENDAR_DIR . 'app/Services/Libs/action-scheduler/action-scheduler.php' );

    add_action('plugins_loaded', function () use ($app) {

        if (file_exists(FLUENT_CALENDAR_DIR . 'app/Saas/init.php')) {
            require_once FLUENT_CALENDAR_DIR . 'app/Saas/init.php';
        }

        do_action('fluent_calendar_loaded', $app);
    });


    add_filter('cron_schedules', function ($schedules) {
        if (!is_array($schedules)) {
            $schedules = [];
        }

        if (!isset($schedules['fluent_cal_every_minute'])) {
            $schedules['fluent_cal_every_minute'] = array(
                'interval' => 60,
                'display'  => esc_html__('Every Minute (Fluent Calendar)', 'fluent-Calendar'),
            );
        }

        return $schedules;
    }, 11);
};
