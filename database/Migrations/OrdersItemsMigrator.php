<?php

namespace FluentBooking\Database\Migrations;

class OrdersItemsMigrator
{
    public static $tableName = 'fcal_order_items';
	public static function migrate() {
		global $wpdb;

		$charsetCollate = $wpdb->get_charset_collate();
		$table          = $wpdb->prefix . static::$tableName;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "CREATE TABLE $table (
                `id` BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                `order_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `booking_id` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `item_name` TEXT NOT NULL,
                `quantity` INT NOT NULL DEFAULT '0',
                `item_price` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `item_total` DECIMAL(18,9) NOT NULL DEFAULT '0.000000000',
                `rate` DECIMAL(18,9) NOT NULL DEFAULT '1.000000000',
                `line_meta` TEXT NULL,
                `created_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP NULL
            ) $charsetCollate;";
			dbDelta( $sql );
		}
	}
}
