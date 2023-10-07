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

add_action('init', function () {
    if(!isset($_GET['fcal'])) {
        return;
    }

    $availabilities = \FluentBooking\App\Models\Availability::with(['calendar'])
        ->orderBy('id', 'desc')
        ->get();

    dd($availabilities->toArray());


    $meta = \FluentBooking\App\Models\Meta::where('object_type', '_google_user_token')
        ->where('object_id', 1)
        ->first();

    $settings = $meta->value;

    dd($settings);

//    $body = [
//        'client_id'     => '350541699442-6tc8e3qd6mrudtedu45bt81cb3dt48dj.apps.googleusercontent.com',
//        'client_secret' => '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',
//        'redirect_uri'  => 'https://fluentbookings.com/wp-admin/admin-ajax.php?action=fluent_booking_g_auth',
//        'grant_type'    => 'refresh_token',
//        'code' => '4/0AfJohXmQ2-PoHaUZOJSOstd6Mo3AbCgZQ2dH26IH-yVubOGDlbR0YqeelwKzapHtwDQDSQ',
//        'refresh_token' => '1//0gpC6oxACxcReCgYIARAAGBASNwF-L9IrzLWMQTxT50Zlugp1LEgzgwJRq_4J0YcFb8Ca289YSUQ0e5zr_zgPuLjiFFG4VCueZMM',
//    ];
//
//    $response = wp_remote_request('https://oauth2.googleapis.com/token', [
//        'body' => $body,
//        'method' => 'POST'
//    ]);

    $meta = \FluentBooking\App\Models\Meta::where('key', 'google_calendar_auth')->first();

    $settings = $meta->value;

    $headers = [
        'Authorization' => 'Bearer '.$settings['access_token'],
        'Content-Type'  => 'application/json; charset=utf-8'
    ];

    // calendarlists
    // https://www.googleapis.com/calendar/v3/users/me/calendarList
    $request = wp_remote_request('https://www.googleapis.com/calendar/v3/users/me/calendarList', [
        'headers' => $headers,
        'method'  => 'GET'
    ]);

    dd($request);

    // Write your tests here

    $item = \FluentBooking\App\Models\CalendarSlot::where('calendar_id', 1)->where('slug', 'test')->first();

    dd($item);
});
