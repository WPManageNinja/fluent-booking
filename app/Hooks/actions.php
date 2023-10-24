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

use FluentBooking\App\Hooks\Scheduler\FiveMinuteScheduler;
use FluentBooking\App\Hooks\Scheduler\DailyScheduler;

(new FluentBooking\App\Hooks\Handlers\GlobalPaymentHandler)->register();
(new \FluentBooking\App\Hooks\Handlers\FrontEndHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\CleanupHandlers\CleanupHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\NotificationHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\LogHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\AdminMenuHandler())->register();
(new FiveMinuteScheduler())->register();
(new DailyScheduler())->register();

// Load Integrations
require_once FLUENT_BOOKING_DIR . 'app/Services/Integrations/index.php';

(new \FluentBooking\App\Services\LandingPage\LandingPageHandler())->boot();

$app->addAction('init', 'BlockEditorHandler@init');
$app->addAction('wp_ajax_fluent_booking_export_hosts', 'DataExporter@exportBookingHosts');


