<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * - This method should be static.
 * - Ensure proper WordPress checks are in place to prevent unwanted uninstallations.
 *
 * @link       https://github.com/moktermd08
 * @since      1.0.0
 * @package    dualdynamics
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Your uninstallation logic here...

// For example: If you need to drop the table created earlier:
/*
global $wpdb;
$table_name = $wpdb->prefix . 'dualdynamics_fetch_logs';
$wpdb->query("DROP TABLE IF EXISTS $table_name");
*/

