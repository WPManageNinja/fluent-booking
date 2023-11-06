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


    $benchmark = microtime(true);

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
            'RRULE:FREQ=WEEKLY;BYDAY=TH'
        ),
        'status'     => 'confirmed'
    ];


    $recurrence = $item['recurrence'];

    $args = [
        'timeMin' => '2023-10-01T00:00:00Z',
        'timeMax' => '2023-12-31T00:00:00Z',
    ];

    $refDate = new DateTime(Arr::get($item, 'start.dateTime'), new DateTimeZone('UTC'));
    $refDate->setTimezone(new DateTimeZone('Europe/Madrid'));
    $offset = $refDate->getOffset();

    $recurrenceDates = RemoteCalendarHelper::getRruleDates($recurrence, [
        Arr::get($item, 'start.dateTime'),
        Arr::get($item, 'end.dateTime'),
    ], $args['timeMin'], $args['timeMax'], [
        'status' => Arr::get($item, 'status'),
        'offset' => $offset
    ]);


    $formatted = [];
    foreach ($recurrenceDates as $recurrenceDate) {

        $start = new DateTime($recurrenceDate['start'], new DateTimeZone('UTC'));
        $end = new DateTime($recurrenceDate['end'], new DateTimeZone('UTC'));

        $offset = $recurrenceDate['offset'];

        if($offset > 0) {
            $start->add(new DateInterval('PT' . $offset . 'S'));
            $end->add(new DateInterval('PT' . $offset . 'S'));
        } else {
            $start->sub(new DateInterval('PT' . abs($offset) . 'S'));
            $end->add(new DateInterval('PT' . $offset . 'S'));
        }

        $formatted[] = [
            'start'  => $start->format('Y-m-d H:i:s'),
            'end'    => $end->format('Y-m-d H:i:s'),
            'status' => $recurrenceDate['status'],
            'utc'    => $recurrenceDate['start'],
        ];
    }

    dd([$formatted, $recurrenceDates]);

});
