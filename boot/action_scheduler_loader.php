<?php

add_action('plugins_loaded', function () {
    if (class_exists('ActionScheduler_Versions', false) || function_exists('action_scheduler_register_3_dot_7_dot_0')) {
        return;
    }

    require_once FLUENT_BOOKING_DIR . 'vendor/woocommerce/action-scheduler/classes/ActionScheduler_Versions.php';

    if (!function_exists('action_scheduler_initialize_3_dot_7_dot_0')) { // WRCS: DEFINED_VERSION.
        function action_scheduler_initialize_3_dot_7_dot_0()
        {
            if (!class_exists('ActionScheduler', false)) {
                require_once FLUENT_BOOKING_DIR . 'vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php';
                ActionScheduler::init(FLUENT_BOOKING_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php');
            }
        }
    }

    $versions = ActionScheduler_Versions::instance();
    $versions->register('3.7.0', 'action_scheduler_initialize_3_dot_7_dot_0'); // WRCS: DEFINED_VERSION.

    ActionScheduler_Versions::initialize_latest_version();
}, 0, 0);
