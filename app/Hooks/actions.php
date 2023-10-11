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


// FluentBooking Outgoing Webhook
$app->addAction('fluent_booking/after_booking_scheduled', function ($booking) use ($app) {
    $webhook = new \FluentBooking\App\Hooks\Handlers\WebhookHandler($app);
    $webhook->processWebhookResponseForBooking($booking, 'scheduled');
}, 20, 1);

$app->addAction('fluent_booking/booking_schedule_cancelled', function ($booking) use ($app) {
    $webhook = new \FluentBooking\App\Hooks\Handlers\WebhookHandler($app);
    $webhook->processWebhookResponseForBooking($booking, 'cancelled');
}, 20, 1);

$app->addAction('fluent_booking/booking_schedule_completed', function ($booking) use ($app) {
    $webhook = new \FluentBooking\App\Hooks\Handlers\WebhookHandler($app);
    $webhook->processWebhookResponseForBooking($booking, 'completed');
}, 20, 1);

$app->addAction('wp_ajax_fluent_booking_callback_for_background', 'WebhookHandler@handleBackgroundProcessCallback');
$app->addAction('wp_ajax_nopriv_fluent_booking_callback_for_background', 'WebhookHandler@handleBackgroundProcessCallback');


add_action('init', function () {
    if (!isset($_GET['fluent-booking']) || $_GET['fluent-booking'] != 'fluent-booking-beta') {
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
