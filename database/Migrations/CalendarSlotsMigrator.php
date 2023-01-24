<?php

namespace FluentCalendar\Database\Migrations;

class CalendarSlotsMigrator
{
    static $tableName = 'fcal_calendar_slots';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `hash` VARCHAR(192) NULL,
                `user_id` BIGINT(20) UNSIGNED NOT NULL,
                `calendar_id` BIGINT(20) UNSIGNED NOT NULL,
                `duration` INT(11) UNSIGNED NOT NULL,
                `title` VARCHAR(192) NOT NULL,
                `slug` VARCHAR(192) NOT NULL,
                `media_id` BIGINT(20) UNSIGNED,
                `description` LONGTEXT NULL,
                `settings` LONGTEXT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'active',
                `type` VARCHAR(20) NOT NULL DEFAULT 'free',
                `color_schema` VARCHAR(100) NOT NULL DEFAULT 'default',
                `location_type` VARCHAR(100) NOT NULL DEFAULT '',
                `location_heading` TEXT NULL,
                `location_settings` LONGTEXT NULL,
                `max_book_per_slot` INT(10) UNSIGNED NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                KEY `fcal_cs_user_id` (`user_id`),
                KEY `fcal_cs_hash` (`hash`),
                KEY `fcal_cs_status` (`status`),
                KEY `fcal_cs_slug` (`slug`),
                KEY `fcal_cs_type` (`type`)
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }
}
