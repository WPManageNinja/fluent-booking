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
 * @var $app FluentCalendar\Framework\Foundation\Application
 */

(new \FluentCalendar\App\Hooks\Handlers\FrontEndHandler())->register();

$app->addAction('admin_menu', 'AdminMenuHandler@add');

$app->addAction('fluent_calendar/after_booking_scheduled', 'NotificationHandler@bookingScheduled', 10, 3);


/**
 * Enable this line if you want to use custom post types
 */

// $app->addAction('init', 'CPTHandler@registerPostTypes');


if(isset($_GET['cal'])) {
    add_action('init', function () {

        $random_token = bin2hex(random_bytes(32));
        $two_fa_code = str_pad(random_int(0,999999), 6, 0, STR_PAD_LEFT);

// Send $random_token and $2fa_code to the user

        $two_fa_code = '123456';

        $store_me_token = hash_hmac('sha256', $random_token, wp_salt('auth'));
        $store_me_2fa_code = wp_hash_password($two_fa_code);

        var_dump([$store_me_2fa_code, $store_me_token]); die();

        $booking = \FluentCalendar\App\Models\Booking::find(8);
        \FluentCalendar\App\Services\EmailNotificationService::emailToGuestOnBooked($booking);
        \FluentCalendar\App\Services\EmailNotificationService::emailToAdminOnBooked($booking);
        dd($booking);
    });
}


// get 10 users from wordpress users table
