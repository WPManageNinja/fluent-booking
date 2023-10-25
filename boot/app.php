<?php

use FluentBooking\Framework\Foundation\Application;
use FluentBooking\App\Hooks\Handlers\ActivationHandler;
use FluentBooking\App\Hooks\Handlers\DeactivationHandler;

return function ($file) {

    $app = new Application($file);

    register_activation_hook($file, function () use ($app) {
        ($app->make(ActivationHandler::class))->handle();
    });

    register_deactivation_hook($file, function () use ($app) {
        ($app->make(DeactivationHandler::class))->handle();
    });

    require_once( FLUENT_BOOKING_DIR . 'vendor/woocommerce/action-scheduler/action-scheduler.php' );
 
    add_action('plugins_loaded', function () use ($app) {
        do_action('fluent_booking/loaded', $app);

        $licenseManager = new \FluentBooking\App\Services\PluginManager\LicenseManager();
        $licenseManager->initUpdater();

        $licenseMessage = $licenseManager->getLicenseMessages();

        if ($licenseMessage) {
            add_action('admin_notices', function () use ($licenseMessage) {
                $class = 'notice notice-error fc_message';
                $message = $licenseMessage['message'];
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
            });
        }

    });
};
