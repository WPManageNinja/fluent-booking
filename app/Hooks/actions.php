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

use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\Framework\Support\Arr;

(new \FluentBooking\App\Hooks\Handlers\FrontEndHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\CleanupHandlers\CleanupHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\NotificationHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\LogHandler())->register();
(new \FluentBooking\App\Hooks\Handlers\AdminMenuHandler())->register();
(new \FluentBooking\App\Hooks\Scheduler\FiveMinuteScheduler())->register();
(new \FluentBooking\App\Hooks\Scheduler\DailyScheduler())->register();
(new \FluentBooking\App\Services\LandingPage\LandingPageHandler())->boot();

// Global Notification Handler
(new \FluentBooking\App\Hooks\Handlers\GlobalNotificationHandler())->register();

$app->addAction('init', 'BlockEditorHandler@init');
$app->addAction('wp_ajax_fluent_booking_export_hosts', 'DataExporter@exportBookingHosts');

add_action('init', function () {
    if (!isset($_REQUEST['gcal'])) {
        return;
    }

    $item = [
        'start'      => array(
            'dateTime' => '2023-10-26T16:00:00Z',
            'timeZone' => 'Europe/Madrid',
        ),
        'end'        => array(
            'dateTime' => '2023-10-26T18:00:00Z',
            'timeZone' => 'Europe/Madrid',
        ),
        'recurrence' => array(
            '0' => 'RRULE:FREQ=WEEKLY;BYDAY=TH',
        ),
        'status'     => 'confirmed'
    ];

    $recurrence = Arr::get($item, 'recurrence.0');

    $args = [
        'timeMin' => '2023-12-01T00:00:00Z',
        'timeMax' => '2023-12-31T00:00:00Z',
    ];

    $recurrenceDates = RemoteCalendarHelper::getRruleDates($recurrence, [
        Arr::get($item, 'start.dateTime'),
        Arr::get($item, 'end.dateTime'),
    ], $args['timeMin'], $args['timeMax'], [
        'status' => Arr::get($item, 'status'),
    ]);

    $formatted = [];
    foreach ($recurrenceDates as $recurrenceDate) {
        $formatted[] = [
            'start'  => \FluentBooking\App\Services\DateTimeHelper::convertFromUtc($recurrenceDate['start'], 'Europe/Madrid'),
            'end'    => \FluentBooking\App\Services\DateTimeHelper::convertFromUtc($recurrenceDate['end'], 'Europe/Madrid'),
            'status' => $recurrenceDate['status'],
        ];
    }

    dd([$formatted, $recurrenceDates]);

});
