<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              https://github.com/moktermd08
 * @since             1.0.0
 * @package           dualdynamics
 *
 * @wordpress-plugin
 * Plugin Name:       dualdynamics
 * Plugin URI:        https://github.com/moktermd08/dueldynamics
 * Description:        RESTful API CRUD to convert RSS feed into WordPress posts and creating a custom widget for the latest 5 posts using Test-Driven Development (TDD) for DMG media 
 * Version:           1.0.0
 * Author:            Mokter Hossian
 * Author URI:        https://github.com/moktermd08
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dualdynamics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DUALDYNAMICS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dualdynamics-activator.php
 */
function activate_dualdynamics() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
    dualdynamics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dueldynamics-deactivator.php
 */
function deactivate_dualdynamics() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
   dualdynamics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dualdynamics' );
register_deactivation_hook( __FILE__, 'deactivate_dualdynamics' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/dualdynamics.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path(__FILE__) . 'includes/feed-importer-abstract.php';

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path(__FILE__) . 'includes/feed-importer-factory.php';


// Import the Admin class
require_once plugin_dir_path(__FILE__) . 'admin/dashboard.php';

// log fetch requests 
require_once plugin_dir_path(__FILE__) . 'includes/fetch-logger.php';

/**
 * Custom admin action to import CNN RSS posts.
 */
function dualdynamics_import_cnn_rss() {
    // Check if user has the required capability
    if (!current_user_can('manage_options')) {
        wp_die('You are not allowed to run this operation.');
    }

    // Check if the custom action is triggered
    if (isset($_GET['import_cnn_rss'])) {
        $cnn_importer = RSSFeedImporterFactory::create('cnn');
        $response = $cnn_importer->import_rss_to_posts();
        if (isset($response['success']) && $response['success']) {
            wp_die('RSS Posts imported successfully!', 'Success', array('response' => 200));
        } else {
            wp_die('Failed to import RSS Posts.', 'Failed', array('response' => 500));
        }
    }
}
add_action('admin_init', 'dualdynamics_import_cnn_rss');


/**
 * Register admin functionalities and hooks.
 */
function setup_dualdynamics_admin() {
    $admin = new dualdynamics_Admin('dualdynamics', DUALDYNAMICS_VERSION);
    
    // Register the admin page
    add_action('admin_menu', array($admin, 'add_admin_menu'));
    
    // Register the handling of the form submission
    add_action('admin_init', array($admin, 'handle_rss_import'));
}
add_action('admin_init', 'setup_dualdynamics_admin');


// Hook for adding admin menus
function dualdynamics_admin_menu() {
    $admin = new dualdynamics_Admin('dualdynamics', DUALDYNAMICS_VERSION);
    $admin->add_admin_menu();
}

// Action for admin menu setup
add_action('admin_menu', 'dualdynamics_admin_menu');
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_dualdynamics() {

    $plugin = new dualdynamics();
    $plugin->run();

}
run_dualdynamics();