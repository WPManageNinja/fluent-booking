<?php defined('ABSPATH') or die;

/*
Plugin Name: Fluent Calendar
Description: Fluent Calendar WordPress Plugin
Version: 1.0.0
Author: 
Author URI: 
Plugin URI: 
License: GPLv2 or later
Text Domain: fluent-calendar
Domain Path: /language
*/

define('FLUENT_CALENDAR_DIR', plugin_dir_path(__FILE__));
define('FLUENT_CALENDAR_URL', plugin_dir_url(__FILE__));

require __DIR__.'/vendor/autoload.php';

call_user_func(function($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__.'/boot/app.php'));



register_deactivation_hook(
    __FILE__,
    'social_ninja_delete_all_data'
);

function social_ninja_delete_all_data()
{
    global $wpdb;
    // truncate tables name wpsr_caches
    $wpdb->query("TRUNCATE TABLE". $wpdb->prefix."wpsr_caches");
    $wpdb->query("TRUNCATE TABLE". $wpdb->prefix."wpsr_reviews");
}
