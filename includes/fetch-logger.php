<?php

class Dualdynamics_Fetch_Logger {

    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'dualdynamics_fetch_logs';

        // Hook to create database table on plugin activation
        register_activation_hook(__FILE__, [$this, 'create_log_table']);
    }

    // Create a table to store fetch logs
    public function create_log_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            fetch_url varchar(255) NOT NULL,
            status varchar(255) NOT NULL,
            fetch_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Store fetch status to the database
    public function log_fetch($url, $status) {
        global $wpdb;

        $wpdb->insert(
            $this->table_name,
            [
                'fetch_url'  => $url,
                'status'     => $status,
                'fetch_time' => current_time('mysql')
            ]
        );
    }

    // Get all fetch logs
    public function get_fetch_logs() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY fetch_time DESC");
    }
}
