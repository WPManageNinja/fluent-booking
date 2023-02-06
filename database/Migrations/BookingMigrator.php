<?php

namespace FluentCalendar\Database\Migrations;

class BookingMigrator
{
    static $tableName = 'fcal_bookings';

    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . static::$tableName;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `hash` VARCHAR(192) NULL,
                `calendar_id` BIGINT(20) UNSIGNED NOT NULL,
                `slot_id` BIGINT(20) UNSIGNED NOT NULL,
                `parent_id` BIGINT(20) UNSIGNED NULL,
                `person_user_id` BIGINT(20) UNSIGNED NULL,
                `person_contact_id` BIGINT(20) UNSIGNED NULL,
                `person_time_zone` VARCHAR(100) NULL,
                `start_time` TIMESTAMP NULL,
                `end_time` TIMESTAMP NULL,
                `slot_minutes` INT(11) UNSIGNED NOT NULL,
                `first_name` VARCHAR(192) NULL,
                `last_name` VARCHAR(192) NULL,
                `email` VARCHAR(192) NULL,
                `message` TEXT NULL,
                `internal_note` TEXT NULL,
                `phone` VARCHAR(100) NULL,
                `country` VARCHAR(100) NULL,
                `ip_address` VARCHAR(192) NULL,
                `browser` VARCHAR(192) NULL,
                `device` VARCHAR(192) NULL,
                `other_info` LONGTEXT NULL,
                `location_details` LONGTEXT NULL,
                `reminder_stage` VARCHAR(192) NULL DEFAULT 'init',
                `last_reminder_sent` TIMESTAMP NULL,
                `next_reminder` TIMESTAMP NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'scheduled',
                `source` VARCHAR(20) NOT NULL DEFAULT 'web',
                `source_id` BIGINT(20) UNSIGNED NULL,
                `utm_source` VARCHAR(192) NULL DEFAULT '',
                `utm_medium` VARCHAR(192) NULL DEFAULT '',
                `utm_campaign` VARCHAR(192) NULL DEFAULT '',
                `utm_term` VARCHAR(192) NULL DEFAULT '',
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                KEY `fcal_b_parent_id` (`parent_id`),
                KEY `fcal_b_hash` (`hash`),
                KEY `fcal_b_calendar_id` (`calendar_id`),
                KEY `fcal_b_slot_id` (`slot_id`),
                KEY `fcal_b_reminder_stage` (`reminder_stage`),
                KEY `fcal_b_next_reminder` (`next_reminder`),
                KEY `fcal_b_start_time` (`start_time`)
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }
}
