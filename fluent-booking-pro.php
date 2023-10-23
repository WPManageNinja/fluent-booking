<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Booking Pro
Description: Fluent Booking WordPress Plugin
Version: 1.0.5
Author: Meeting scheduling made easy
Author URI: https://wpmanageninja.com
Plugin URI: https://fluentbooking.com
License: GPLv2 or later
Text Domain: fluent-booking-pro
Domain Path: /language
*/
define('FLUENT_BOOKING_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_URL', plugin_dir_url(__FILE__));
define('FLUENT_BOOKING_PRO_DIR_FILE', __FILE__);
define('FLUENT_BOOKING_VERSION', '1.0.5');
define('FLUENT_BOOKING_ASSETS_VERSION', '1.0.5');

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));
