<?php

/**
 * All registered action's handlers should be in app\Hooks\Handlers,
 * addAction is similar to add_action and addCustomAction is just a
 * wrapper over add_action which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomAction('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_action('slug-foo', ['FooHandler', 'handleFoo']).
 */

/**
 * @var $app FluentBooking\Framework\Foundation\Application
 */

/*
 * Register all the grouped action handlers
 */
(new \FluentBooking\App\Hooks\Handlers\FrontEndHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\CleanupHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\NotificationHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\LogHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\AdminMenuHandler())->register();


// Load Integrations
require_once FLUENT_BOOKING_DIR . 'app/Services/Integrations/index.php';


(new \FluentBooking\App\Services\LandingPage\LandingPageHandler())->boot();


$app->addAction('init', 'BlockEditorHandler@init');
$app->addAction('wp_ajax_fluent_booking_export_hosts', 'DataExporter@exportBookingHosts');


(new FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler)->register();

(new FluentBooking\App\Services\PluginManager\Bootstrap())->register();

add_action('init', function () {
    if (!isset($_GET['fluent-booking']) || $_GET['fluent-booking'] != 'fluent-booking-beta') {
        return;
    }

    if(!current_user_can('manage_options')) {
        return;
    }

    $tables = [
        'fcal_booking_activity',
        'fcal_booking_hosts',
        'fcal_booking_meta',
        'fcal_bookings',
        'fcal_calendar_events',
        'fcal_calendars',
        'fcal_meta'
    ];

    global $wpdb;
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
    }
    // run the migrations
    require_once FLUENT_BOOKING_DIR . 'database/DBMigrator.php';
    \FluentBooking\Database\DBMigrator::run();
    wp_redirect(admin_url('admin.php?page=fluent-booking#/'));
    exit();
});
