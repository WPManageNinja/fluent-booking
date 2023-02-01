<?php

namespace FluentCalendar\Database;

use FluentCalendar\Database\Migrations\BookingMigrator;
use FluentCalendar\Database\Migrations\BookingUserMigrator;
use FluentCalendar\Database\Migrations\CalendarMigrator;
use FluentCalendar\Database\Migrations\CalendarSlotsMigrator;
use FluentCalendar\Database\Migrations\MetaMigrator;

class DBMigrator
{
    public static function run($network_wide = false)
    {
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');

        if (is_multisite() && $network_wide) {
            global $wpdb;
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                static::migrate();
                restore_current_blog();
            }
        } else {
            static::migrate();
        }
    }

    private static function migrate()
    {
        CalendarMigrator::migrate();
        CalendarSlotsMigrator::migrate();
        BookingMigrator::migrate();
        BookingUserMigrator::migrate();
        MetaMigrator::migrate();
    }
}
