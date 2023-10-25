<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Booking
Description: Appointment Scheduling WordPress Plugin
Version: 1.0.0
Author: techjewel
Author URI: https://jewel.im
Plugin URI: https://fluentbooking.com
License: GPLv2 or later
Text Domain: fluent-booking
Domain Path: /language
*/

if (defined('FLUENT_BOOKING_VERSION')) {
    return;
}

define('FLUENT_BOOKING_VERSION', '1.0.0');
define('FLUENT_BOOKING_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_URL', plugin_dir_url(__FILE__));
define('FLUENT_BOOKING_ASSETS_VERSION', '1.0.0');

require __DIR__ . '/vendor/autoload.php';

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/fluent_app.php'));
