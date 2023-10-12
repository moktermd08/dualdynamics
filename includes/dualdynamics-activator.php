<?php

/**
 * Fired during the activation of the Dualdynamics plugin.
 *
 * This file contains code that gets executed when the Dualdynamics plugin
 * is activated. Typically, this involves tasks such as setting up database
 * tables, adding default options, or flushing rewrite rules.
 *
 * @link       https://github.com/moktermd08
 * @since      1.0.0
 *
 * @package    dualdynamics
 * @subpackage dualdynamics/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the Dualdynamics plugin's activation.
 *
 * @since      1.0.0
 * @package    dualdynamics
 * @subpackage dualdynamics/includes
 * @author     Mokter Hossian <mokter897@gmail.com>
 */
class dualdynamics_Activator {

    /**
     * Executes tasks needed for the activation of the plugin.
     *
     * This method is called when the plugin is activated. Here, we'll 
     * ensure the necessary database table for the fetch logger is created.
     *
     * @since    1.0.0
     */
    public static function activate() {
        require_once plugin_dir_path(__FILE__) . 'class-dualdynamics-fetch-logger.php';
        
        // Instantiate the logger and create its table.
        $logger = new Dualdynamics_Fetch_Logger();
        $logger->create_log_table();
    }

}
