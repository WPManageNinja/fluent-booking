<?php defined('ABSPATH') or die;
use FluentBooking\App\Hooks\Handlers\GlobalNotificationHandler;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Booking;

/*
Plugin Name: Fluent Booking
Description: Fluent Booking WordPress Plugin
Version: 1.0.0
Author: Meeting scheduling made easy
Author URI: https://wpmanageninja.com
Plugin URI: https://fluentbooking.com
License: GPLv2 or later
Text Domain: fluent-booking
Domain Path: /language
*/

define('FLUENT_BOOKING_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_URL', plugin_dir_url(__FILE__));
define('FLUENT_BOOKING_ASSETS_VERSION', time());
define('FLUENT_BOOKING_DIR_FILE', __FILE__);
define('FLUENT_BOOKING_VERSION', '0.1');

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));

add_action('init', function () {
    $booking = Booking::find(2);
    $slot = CalendarSlot::find(1);

    $GlobalNotificationHandler = new GlobalNotificationHandler();

    $GlobalNotificationHandler->globalNotify($booking, $slot);
});
