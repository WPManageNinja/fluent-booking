<?php

namespace FluentBooking\Database\Migrations;

class BookingMetaMigrator
{
    /**
     * Migrate the table.
     *
     * @return void
     */
    public static function migrate()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix .'fcal_booking_meta';

        $indexPrefix = $wpdb->prefix .'fcal_bmt_';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
            $sql = "CREATE TABLE $table (
                `id` BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `event_id` BIGINT NULL,
                `meta_key` VARCHAR(192) NOT NULL,
                `value` LONGTEXT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL,
                 INDEX `{$indexPrefix}_bmto_id_idx` (`event_id` ),
                 INDEX `{$indexPrefix}_bmto_id_key` (`meta_key` )
            ) $charsetCollate;";
            dbDelta($sql);
        }
    }
}
