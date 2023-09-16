<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Booking
Description: Fluent Calendar WordPress Plugin
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

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));
